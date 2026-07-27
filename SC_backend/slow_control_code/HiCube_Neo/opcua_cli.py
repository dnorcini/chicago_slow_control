#!/usr/bin/env python3
# Minimal OPC UA CLI: browse/grep/read/write/ns + list writable vars, with optional login & security

import argparse, sys, time
from opcua import Client, ua

def connect(url, user=None, password=None, security=None):
    c = Client(url)
    if security:
        # Example: "Basic256Sha256,SignAndEncrypt,client_cert.der,client_key.pem"
        # Leave unset if your server allows no-security on LAN.
        c.set_security_string(security)
    if user is not None:
        c.set_user(user)
        c.set_password(password or "")
    c.connect()
    return c

def nodeclass_name(cls):
    try:
        return ua.NodeClass(cls).name
    except Exception:
        return str(cls)

def browse_recursive(c, node, depth=0, maxdepth=3, only_vars=False, prefix=""):
    if depth > maxdepth:
        return
    try:
        bn = node.get_browse_name()
        cls = node.get_node_class()
    except Exception as e:
        print(prefix + f"? {node} (error: {e})")
        return
    if not only_vars or cls == ua.NodeClass.Variable:
        print(prefix + f"{nodeclass_name(cls):>8}  {node.nodeid}  {bn}")
    # Follow hierarchical references forward
    try:
        for ref in node.get_references(refs=ua.ObjectIds.HierarchicalReferences,
                                       direction=ua.BrowseDirection.Forward):
            child = c.get_node(ref.NodeId)
            browse_recursive(c, child, depth+1, maxdepth, only_vars, prefix+"  ")
    except Exception:
        # Fallback: generic children
        try:
            for child in node.get_children():
                browse_recursive(c, child, depth+1, maxdepth, only_vars, prefix+"  ")
        except Exception:
            pass

def cmd_browse(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        start = c.get_node(args.start) if args.start else c.get_objects_node()
        browse_recursive(c, start, maxdepth=args.depth, only_vars=args.variables)
    finally:
        c.disconnect()

def cmd_grep(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        targets = [t.lower() for t in args.terms]
        def walk(n, depth=0, maxdepth=args.depth):
            if depth > maxdepth: return
            try:
                bn = n.get_browse_name()
                cls = n.get_node_class()
                name = str(bn.Name).lower()
            except Exception:
                name, cls = "", None
            if cls == ua.NodeClass.Variable and any(t in name for t in targets):
                print(f"Variable  {n.nodeid}  {bn}")
            try:
                for ref in n.get_references(refs=ua.ObjectIds.HierarchicalReferences,
                                            direction=ua.BrowseDirection.Forward):
                    walk(c.get_node(ref.NodeId), depth+1, maxdepth)
            except Exception:
                try:
                    for ch in n.get_children():
                        walk(ch, depth+1, maxdepth)
                except Exception:
                    pass
        root = c.get_objects_node()
        walk(root)
    finally:
        c.disconnect()

def cmd_read(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        for nid in args.nodeid:
            n = c.get_node(nid)
            try:
                dv = n.get_data_value()
                print(f"{nid}  {n.get_browse_name()}: {dv.Value.Value}  "
                      f"(status={dv.StatusCode}, ts={dv.SourceTimestamp})")
            except ua.uaerrors._auto.BadAttributeIdInvalid:
                print(f"{nid}: not a Variable (cannot read Value)")
            except ua.UaStatusCodeError as e:
                print(f"{nid}: read rejected: {e}")
            except Exception as e:
                print(f"{nid}: read error: {e}")
    finally:
        c.disconnect()

def parse_scalar(valstr, typestr):
    tmap = {
        "double": ua.VariantType.Double,
        "float":  ua.VariantType.Float,
        "int":    ua.VariantType.Int32,
        "int32":  ua.VariantType.Int32,
        "uint32": ua.VariantType.UInt32,
        "bool":   ua.VariantType.Boolean,
        "boolean":ua.VariantType.Boolean,
        "string": ua.VariantType.String,
    }
    vt = tmap.get(typestr.lower())
    if vt is None:
        raise ValueError(f"Unknown type '{typestr}'. Try: double,float,int,bool,string.")
    if vt in (ua.VariantType.Double, ua.VariantType.Float): pyv = float(valstr)
    elif vt in (ua.VariantType.Int32, ua.VariantType.UInt32): pyv = int(valstr)
    elif vt == ua.VariantType.Boolean: pyv = valstr.lower() in ("1","true","yes","on")
    elif vt == ua.VariantType.String: pyv = valstr
    else: pyv = valstr
    return ua.Variant(pyv, vt)

def cmd_write(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        n = c.get_node(args.nodeid)
        v = parse_scalar(args.value, args.type)
        rc = n.set_value(v)
        print(f"Write status: {rc}")
        dv = n.get_data_value()
        print(f"After write: {dv.Value.Value}  (status={dv.StatusCode}, ts={dv.SourceTimestamp})")
    except ua.UaStatusCodeError as e:
        print(f"Server rejected write: {e}")
    except Exception as e:
        print(f"Write error: {e}")
    finally:
        c.disconnect()

def cmd_ns(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        nsarr = c.get_namespace_array()
        for idx, uri in enumerate(nsarr):
            print(f"ns={idx}  {uri}")
    finally:
        c.disconnect()

def is_writable_var(n):
    # Returns (is_writable_for_user, accessLevel, userAccessLevel or None)
    al = ual = None
    try:
        al = n.get_attribute(ua.AttributeIds.AccessLevel).Value.Value
    except Exception:
        pass
    try:
        ual = n.get_attribute(ua.AttributeIds.UserAccessLevel).Value.Value
    except Exception:
        ual = None
    # Bit 0x02 = CurrentWrite
    writable = (al is not None and (al & 0x02) != 0)
    if ual is not None:
        writable = writable and ((ual & 0x02) != 0)
    return writable, al, ual

def cmd_writable(args):
    c = connect(args.url, args.user, args.password, args.security)
    try:
        start = c.get_node(args.start) if args.start else c.get_objects_node()
        terms = [t.lower() for t in (args.contains or [])]
        count = 0
        def walk(n, depth=0):
            nonlocal count
            if depth > args.depth: return
            try:
                if n.get_node_class() == ua.NodeClass.Variable:
                    wr, al, ual = is_writable_var(n)
                    if wr:
                        bn = n.get_browse_name()
                        name = str(bn.Name)
                        if terms and not any(t in name.lower() for t in terms):
                            pass
                        else:
                            try:
                                dtype = n.get_data_type_as_variant_type().name
                            except Exception:
                                dtype = "?"
                            try:
                                val = n.get_value()
                            except Exception:
                                val = "(read-failed)"
                            print(f"WRITABLE  {n.nodeid}  {name}  type={dtype}  val={val}  "
                                  f"Access=0x{(al or 0):02x}" + (f" UserAccess=0x{ual:02x}" if ual is not None else ""))
                            count += 1
            except Exception:
                pass
            # recurse
            try:
                for ref in n.get_references(refs=ua.ObjectIds.HierarchicalReferences,
                                            direction=ua.BrowseDirection.Forward):
                    walk(c.get_node(ref.NodeId), depth+1)
            except Exception:
                pass
        walk(start)
        if count == 0:
            print("No writable Variables found at this depth or with the given filter.")
    finally:
        c.disconnect()

def main():
    ap = argparse.ArgumentParser(description="OPC UA CLI (browse/grep/read/write/ns/writable) with optional login & security")
    # Global options
    ap.add_argument("--user", help="Username for OPC UA (optional)")
    ap.add_argument("--password", help="Password for OPC UA (optional)")
    ap.add_argument("--security", help="Security string, e.g. 'Basic256Sha256,SignAndEncrypt,client_cert.der,client_key.pem'")
    # Positional
    ap.add_argument("url", help="Endpoint URL, e.g. opc.tcp://10.120.1.43:4840")
    sp = ap.add_subparsers(dest="cmd")  # no "required" in Python 3.6
    
    p = sp.add_parser("browse", help="Browse from Objects or a given node")
    p.add_argument("--start", help="Start nodeid (default: Objects node), e.g. ns=0;i=85")
    p.add_argument("--depth", type=int, default=4, help="Max depth (default 4)")
    p.add_argument("--variables", action="store_true", help="Only show Variable nodes")
    p.set_defaults(func=cmd_browse)

    p = sp.add_parser("grep", help="Find Variable nodes whose BrowseName contains any term")
    p.add_argument("terms", nargs="+", help="Substrings (e.g. pressure speed current)")
    p.add_argument("--depth", type=int, default=6)
    p.set_defaults(func=cmd_grep)

    p = sp.add_parser("read", help="Read Value of one or more nodes")
    p.add_argument("nodeid", nargs="+", help='NodeIds like "ns=2;i=2001" or "ns=2;s=Path" (quote to keep the ;)')
    p.set_defaults(func=cmd_read)

    p = sp.add_parser("write", help="Write a scalar value to a Variable node (careful!)")
    p.add_argument("nodeid")
    p.add_argument("type", help="Type: double,float,int,bool,string")
    p.add_argument("value", help="Value text, e.g. 1000 or true")
    p.set_defaults(func=cmd_write)

    p = sp.add_parser("ns", help="Show NamespaceArray (URI ↔ ns index)")
    p.set_defaults(func=cmd_ns)

    p = sp.add_parser("writable", help="List writable Variables (checks AccessLevel/UserAccessLevel)")
    p.add_argument("--start", help="Start nodeid (default: Objects node)")
    p.add_argument("--depth", type=int, default=6, help="Max depth (default 6)")
    p.add_argument("--contains", nargs="*", help="Optional name filters (e.g. Start Stop Enable Setpoint)")
    p.set_defaults(func=cmd_writable)

    args = ap.parse_args()
    if args.cmd is None:
        ap.error("a subcommand is required")

    args.func(args)

if __name__ == "__main__":
    main()
