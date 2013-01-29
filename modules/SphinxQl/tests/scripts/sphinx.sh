#!/bin/sh

DIRNAME=`dirname $0`
source $DIRNAME/env.sh

pid=`grep pid_file $config | awk '{print $3}'`
command="$searchd --config ${config}"

echo_name () {
    echo -n " `basename $0 | awk '{print a[split($1, a, ".")-1]}'`"
}

case "$1" in
start|"")
        $command
        ;;
stop)
                [ -f $pid ] && kill `cat $pid` && sleep 1 && echo "Searchd process stopped"
        ;;
restart)
        $0 stop
        $0 start
        ;;
*)
        echo "Usage: `basename $0` {start|stop|restart}" >&2
        ;;
esac

exit 0