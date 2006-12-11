#!/usr/bin/env bash

build_msntp() {
	cd /usr/ports/net/msntp

	make clean
	make

	return 0
}

install_msntp() {
	cd /usr/ports/net/msntp

	install -vs work/msntp*/msntp $FREENAS/usr/local/bin

	echo '#!/bin/sh
# write our PID to file
echo $$ > $1

# execute msntp in endless loop; restart if it
# exits (wait 1 second to avoid restarting too fast in case
# the network is not yet setup)
while true; do
	/usr/local/bin/msntp -r -P no -l $2 -x $3 $4
	sleep 1
done' > $FREENAS/usr/local/bin/runmsntp.sh

	chmod +x $FREENAS/usr/local/bin/runmsntp.sh

	return 0
}
