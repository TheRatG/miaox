#!/bin/sh

DIRNAME=`dirname $0`
source $DIRNAME/env.sh

usage="Usage: $0 [-i index list with delimetr ',', default all] [-v verbose mode]";

verbose=0;
index='--all';
while getopts ":i:v" opt; do
    case $opt in
      v  )
      	verbose=1 ;;
      i  )
      	index=$OPTARG ;;
      \? ) echo $usage
           exit 1 ;;
    esac
done

shift $(($OPTIND - 1))

COMMAND_STRING="$indexer --config $config $index --rotate";
if [ $verbose = 1 ];	then
	echo -e "Start command: ${COMMAND_STRING}";
else
	COMMAND_STRING="${COMMAND_STRING} --quiet";
fi;

${COMMAND_STRING};