/* Program for reading HiCube_Neo pump with OPC-UA */
/* and putting said readings in to a mysql database. */
/* Cinyu Zhu, Johns Hopkins, 2025 */

#include "SC_aux_fns.h"
#include "SC_db_interface.h"
#include "SC_sensor_interface.h"

#include <errno.h>
#include <stdio.h>
#include <string.h>

#include <open62541/client.h>
#include <open62541/client_config_default.h>

// Default instrument name
#define INSTNAME "HiCube_Neo"
#define NODE_SPEED "ns=1;s=P1_398_ActualSpd"
#define NODE_POWER "ns=1;s=P1_316_DrvPower"
#define NODE_STATUS "ns=1;s=SYS_STATUS" /* 1=off, 12=on */

static UA_Client *client = NULL;

/* Convenience: parse "ns=1;s=..." into an owning NodeId */
static UA_NodeId parseNode(const char *idStrRaw) {
  // Trim leading/trailing spaces
  char buf[256];
  size_t n = 0;
  if (!idStrRaw)
    return UA_NODEID_NULL;
  while (*idStrRaw == ' ' || *idStrRaw == '\t' || *idStrRaw == '\n' ||
         *idStrRaw == '\r')
    idStrRaw++;
  for (const char *p = idStrRaw; *p && n < sizeof(buf) - 1; ++p)
    buf[n++] = *p;
  while (n > 0 && (buf[n - 1] == ' ' || buf[n - 1] == '\t' ||
                   buf[n - 1] == '\n' || buf[n - 1] == '\r'))
    n--;
  buf[n] = '\0';

  unsigned ns = 0;
  if (sscanf(buf, "ns=%u;", &ns) != 1) {
    // Fallback: assume vendor ns=1 if not present
    ns = 1;
  }
  char *semi = strchr(buf, ';');
  if (!semi)
    return UA_NODEID_STRING_ALLOC((UA_UInt16)ns, buf);

  if (strncmp(semi + 1, "s=", 2) == 0) {
    return UA_NODEID_STRING_ALLOC((UA_UInt16)ns, semi + 3);
  } else if (strncmp(semi + 1, "i=", 2) == 0) {
    unsigned idnum = 0;
    if (sscanf(semi + 3, "%u", &idnum) == 1)
      return UA_NODEID_NUMERIC((UA_UInt16)ns, (UA_UInt32)idnum);
  }
  // Default to string if unknown
  return UA_NODEID_STRING_ALLOC((UA_UInt16)ns, semi + 1);
}

int inst_dev;
#define _def_set_up_inst
static void dump_namespace_array(void) {
  /* Build a ReadRequest for ns=0;i=2256, AttributeId=Value */
  UA_ReadRequest req;
  UA_ReadRequest_init(&req);
  req.nodesToReadSize = 1;
  req.nodesToRead =
      (UA_ReadValueId *)UA_Array_new(1, &UA_TYPES[UA_TYPES_READVALUEID]);
  if (!req.nodesToRead) {
    log_err("[HiCubeNeo] NamespaceArray: UA_Array_new failed\n");
    return;
  }

  UA_ReadValueId_init(&req.nodesToRead[0]);
  req.nodesToRead[0].nodeId =
      UA_NODEID_NUMERIC(0, 2256); // Server_NamespaceArray
  req.nodesToRead[0].attributeId = UA_ATTRIBUTEID_VALUE;
  req.maxAge = 0.0;
  req.timestampsToReturn = UA_TIMESTAMPSTORETURN_NEITHER;

  UA_ReadResponse resp = UA_Client_Service_read(client, req);

  UA_ReadResponse_clear(&resp);
  UA_Array_delete(req.nodesToRead, 1, &UA_TYPES[UA_TYPES_READVALUEID]);
}

int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a) {

  if (!i_s || !i_s->dev_address || strlen(i_s->dev_address) == 0) {
    log_err("[HiCubeNeo] ERROR: empty endpoint in i_s->dev_address\n");
    return 1;
  }

  client = UA_Client_new();
  UA_ClientConfig *cfg = UA_Client_getConfig(client);
  UA_ClientConfig_setDefault(cfg);

  char endpoint[128];
  snprintf(endpoint, sizeof(endpoint), "opc.tcp://%s:4840", i_s->dev_address);
  log_err("[HiCubeNeo] set_up_inst: endpoint=\"%s\"\n", endpoint);
  UA_StatusCode st = UA_Client_connect(client, endpoint);
  if (st != UA_STATUSCODE_GOOD) {
    log_err("[HiCubeNeo] OPC-UA connect failed: 0x%08x (%s)\n", st,
            UA_StatusCode_name(st));
    UA_Client_delete(client);
    client = NULL;
    return 1;
  }
  dump_namespace_array();

  return 0;
}

#define _def_clean_up_inst
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a) {
  if (client) {
    UA_Client_disconnect(client);
    UA_Client_delete(client);
    client = NULL;
  }
}

/* Service-level read of a scalar numeric, with detailed debug */
static int read_double(const char *nodeIdStr, double *out) {
  if (!client) {
    // log_err("[HiCubeNeo] read_double: client=NULL\n");
    return 1;
  }

  UA_NodeId nid = parseNode(nodeIdStr);

  UA_ReadRequest req;
  UA_ReadRequest_init(&req);
  req.nodesToReadSize = 1;
  req.nodesToRead =
      (UA_ReadValueId *)UA_Array_new(1, &UA_TYPES[UA_TYPES_READVALUEID]);
  if (!req.nodesToRead) {
    UA_NodeId_clear(&nid);
    return 1;
  }

  UA_ReadValueId_init(&req.nodesToRead[0]);
  UA_StatusCode cst = UA_NodeId_copy(&nid, &req.nodesToRead[0].nodeId);
  req.nodesToRead[0].attributeId = UA_ATTRIBUTEID_VALUE;
  req.maxAge = 0.0;
  req.timestampsToReturn = UA_TIMESTAMPSTORETURN_NEITHER;

  UA_ReadResponse resp = UA_Client_Service_read(client, req);

  int rc = 1;
  if (resp.responseHeader.serviceResult == UA_STATUSCODE_GOOD &&
      resp.resultsSize > 0 && resp.results[0].status == UA_STATUSCODE_GOOD) {

    UA_Variant *v = &resp.results[0].value;

    if (UA_Variant_isScalar(v) && v->data && v->type) {
      if (v->type == &UA_TYPES[UA_TYPES_DOUBLE]) {
        *out = *(UA_Double *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_FLOAT]) {
        *out = (double)*(UA_Float *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_INT32]) {
        *out = (double)*(UA_Int32 *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_UINT32]) {
        *out = (double)*(UA_UInt32 *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_INT16]) {
        *out = (double)*(UA_Int16 *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_UINT16]) {
        *out = (double)*(UA_UInt16 *)v->data;
        rc = 0;
      } else if (v->type == &UA_TYPES[UA_TYPES_BYTE]) {
        *out = (double)*(UA_Byte *)v->data;
        rc = 0;
      } else
        log_err("[HiCubeNeo]  Unsupported numeric type: %s\n",
                v->type->typeName);
    } else {
      log_err("[HiCubeNeo]  Variant not scalar or data NULL\n");
    }
  }

  if (rc != 0)
    log_err("[HiCubeNeo] READ FAILED for node=\"%s\"\n", nodeIdStr);

  UA_ReadResponse_clear(&resp);
  UA_Array_delete(req.nodesToRead, 1, &UA_TYPES[UA_TYPES_READVALUEID]);
  UA_NodeId_clear(&nid);
  return rc;
}

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s,
                double *val_out) {
  if (!s_s) {
    log_err("[HiCubeNeo] read_sensor: s_s=NULL\n");
    return 1;
  }

  int rc = 1;
  if (strcmp(s_s->subtype, "rt_spd") == 0) {
    rc = read_double(NODE_SPEED, val_out);
  } else if (strcmp(s_s->subtype, "drv_power") == 0) {
    rc = read_double(NODE_POWER, val_out);
  } else if (strcmp(s_s->subtype, "pump_status") == 0) {
    double status = 0.0;
    rc = read_double(NODE_STATUS, &status);
    if (rc == 0)
      *val_out = status;
  } else {
    log_err("[HiCubeNeo] Unsupported sensor subtype: %s\n",
            s_s->subtype);
    rc = 1;
  }

  return rc;
}

/* Write SYS_STATUS as Float (1.0f = off, 12.0f = on per your mapping) */
static int write_status(int value) {
  if (!client) {
    log_err("[HiCubeNeo] write_status: client=NULL\n");
    return 1;
  }
  /* Build the target NodeId (owns its string) */
  UA_NodeId nid = parseNode(NODE_STATUS);

  /* One WriteValue on the stack */
  UA_WriteValue wv;
  UA_WriteValue_init(&wv);

  /* Deep-copy NodeId into wv (separate ownership) */
  UA_StatusCode cst = UA_NodeId_copy(&nid, &wv.nodeId);
  if (cst != UA_STATUSCODE_GOOD) {
    log_err("[HiCubeNeo] UA_NodeId_copy failed: 0x%08x (%s)\n", cst,
            UA_StatusCode_name(cst));
    UA_NodeId_clear(&nid);
    return 1;
  }

  wv.attributeId = UA_ATTRIBUTEID_VALUE;

  /* Set DataValue.value (a Variant) as FLOAT and mark hasValue=true */
  UA_Float fval = (UA_Float)value; /* 1 -> 1.0f, 12 -> 12.0f */
  UA_Variant_setScalarCopy(&wv.value.value, &fval, &UA_TYPES[UA_TYPES_FLOAT]);
  wv.value.hasValue = true;

  /* Build request pointing to our single WriteValue (no heap array) */
  UA_WriteRequest wReq;
  UA_WriteRequest_init(&wReq);
  wReq.nodesToWriteSize = 1;
  wReq.nodesToWrite = &wv;

  /* Perform write */
  UA_WriteResponse wResp = UA_Client_Service_write(client, wReq);

  /* Success if both the service and the per-node result are Good */
  int ok = (wResp.responseHeader.serviceResult == UA_STATUSCODE_GOOD) &&
           (wResp.resultsSize > 0) && (wResp.results[0] == UA_STATUSCODE_GOOD);

  /* Cleanup only what we own */
  UA_WriteResponse_clear(&wResp);
  UA_Variant_clear(&wv.value.value);
  UA_NodeId_clear(&wv.nodeId);
  UA_NodeId_clear(&nid);

  if (!ok) {
    log_err("[HiCubeNeo] WRITE FAILED (Float %f)\n", (double)fval);
    return 1;
  }

  return 0;
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s) {
  if (!s_s) {
    log_err("[HiCubeNeo] set_sensor: s_s=NULL\n");
    return 1;
  }

  if (strcmp(s_s->subtype, "pump_status") == 0) {
    int set_val = (int)s_s->new_set_val; /* 1=off, 12=on */
    if (set_val != 1 && set_val != 12) {
      log_err("[HiCubeNeo] Invalid pump set value %d\n", set_val);
      return 1;
    }
    return write_status(set_val);
  }

  log_err("[HiCubeNeo] Unsupported sensor subtype for set: %s\n",
          s_s->subtype);
  return 1;
}

#include "main.h"
