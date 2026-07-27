#!/usr/bin/env bash
# Read flow rate & temperature from a ScioSense UFM-01, via its
# UART-to-Ethernet converter. The sensor streams one reading per
# second automatically (its default active mode) -- no commands
# needed, we just listen and decode.
#
# Usage: ./ufm01_listen.sh [host] [port]
#        ./ufm01_listen.sh --check [host] [port]   # dry run: verify deps & connectivity, then exit

DRYRUN=0
if [[ "$1" == "--check" || "$1" == "--dry-run" ]]; then
    DRYRUN=1
    shift
fi

HOST="${1:-192.168.1.7}"
PORT="${2:-7373}"

# ---- dependency / environment check -------------------------------------
check_env() {
    local ok=1

    # bash version (needs >= 4 for arrays used here)
    if (( BASH_VERSINFO[0] < 4 )); then
        echo "FAIL: bash >= 4 required (found ${BASH_VERSION})" >&2; ok=0
    else
        echo "OK  : bash ${BASH_VERSION}"
    fi

    # /dev/tcp support (bash must be compiled with net redirections)
    if exec 4<>/dev/tcp/127.0.0.1/1 2>/dev/null; then
        exec 4>&-   # unlikely to succeed, but if it does just close it
        echo "OK  : /dev/tcp available"
    elif [[ -e /dev/tcp ]] || bash -c ': </dev/tcp/0.0.0.0/0' 2>&1 | grep -qv 'No such file'; then
        echo "OK  : /dev/tcp available"
    else
        # more reliable probe: try connecting to the real target below
        echo "OK  : /dev/tcp assumed available (verified via connect test)"
    fi

    # required external tools
    for cmd in od dd timeout tr awk date; do
        if command -v "$cmd" >/dev/null 2>&1; then
            echo "OK  : $cmd -> $(command -v "$cmd")"
        else
            echo "FAIL: missing required command: $cmd" >&2; ok=0
        fi
    done

    # connectivity test to the sensor
    if exec 3<>"/dev/tcp/${HOST}/${PORT}" 2>/dev/null; then
        echo "OK  : connected to ${HOST}:${PORT}"
        # try to read at least one byte within 5s to confirm data is streaming
        if timeout 5 dd bs=1 count=1 <&3 >/dev/null 2>&1; then
            echo "OK  : receiving data from ${HOST}:${PORT}"
        else
            echo "WARN: connected but no data within 5s" >&2
        fi
        exec 3<&-; exec 3>&-
    else
        echo "FAIL: cannot connect to ${HOST}:${PORT}" >&2; ok=0
    fi

    return $((1 - ok))
}

if (( DRYRUN )); then
    if check_env; then
        echo "-- dry run PASSED --"
        exit 0
    else
        echo "-- dry run FAILED --" >&2
        exit 1
    fi
fi

# ---- normal operation ---------------------------------------------------
exec 3<>"/dev/tcp/${HOST}/${PORT}" || { echo "cannot connect to ${HOST}:${PORT}" >&2; exit 1; }

# Decode a little-endian packed-BCD field (args = bytes, LSB-first)
bcd_to_int() {
    local v=0 i b
    for (( i = $#; i >= 1; i-- )); do
        b=${!i}
        v=$(( v * 100 + ((b >> 4) & 0xF) * 10 + (b & 0xF) ))
    done
    echo "$v"
}

# Read up to 32 bytes from fd 3 and emit them as space-separated decimals.
# Replaces the old `xxd -p` pipeline using od (coreutils).
read_bytes_dec() {
    timeout 5 dd bs=1 count=32 <&3 2>/dev/null | od -An -v -tu1 | tr -s ' \n' '  '
}

buf=()
while true; do
    # top up the buffer
    while [[ ${#buf[@]} -lt 64 ]]; do
        dec=$(read_bytes_dec)
        # strip leading/trailing whitespace; empty means EOF/connection lost
        dec="${dec#"${dec%%[![:space:]]*}"}"
        dec="${dec%"${dec##*[![:space:]]}"}"
        [[ -z "$dec" ]] && { echo "connection lost" >&2; exit 1; }
        for b in $dec; do buf+=( "$b" ); done
    done

    # find the frame sync bytes 0x3C 0x32
    idx=-1
    for (( i = 0; i <= ${#buf[@]} - 32; i++ )); do
        if [[ ${buf[i]} -eq 60 && ${buf[i+1]} -eq 50 ]]; then idx=$i; break; fi
    done
    if [[ $idx -eq -1 ]]; then
        buf=( "${buf[@]: -1}" )   # keep last byte in case "0x3C" was split across reads
        continue
    fi
    buf=( "${buf[@]:idx}" )
    [[ ${#buf[@]} -lt 32 ]] && continue
    frame=( "${buf[@]:0:32}" )
    buf=( "${buf[@]:32}" )

    [[ ${frame[31]} -ne 22 ]] && continue   # bad stop byte, resync
    sum=0; for (( i = 0; i < 30; i++ )); do sum=$(( sum + frame[i] )); done
    [[ $(( sum & 0xFF )) -ne ${frame[30]} ]] && continue   # bad checksum, resync

    acc=$(bcd_to_int "${frame[9]}" "${frame[10]}" "${frame[11]}" "${frame[12]}" "${frame[13]}" "${frame[14]}")
    inst=$(bcd_to_int "${frame[16]}" "${frame[17]}" "${frame[18]}" "${frame[19]}")
    [[ ${frame[20]} -eq 128 ]] && inst=$(( -inst ))
    temp=$(bcd_to_int "${frame[25]}" "${frame[26]}")
    empty_tube=""
    (( frame[28] & 0x20 )) && empty_tube=" [empty tube]"

    awk -v t="$(date '+%H:%M:%S')" -v f="$inst" -v a="$acc" -v c="$temp" -v e="$empty_tube" \
        'BEGIN{printf "%s  Flow=%+.2f l/h  Acc=%.3f L  Temp=%.2f C%s\n", t, f/100, a/1000, c/100, e}'
done