#!/usr/bin/env bash

build_unison() {
	cd /usr/ports/net/unison/

	make clean
	make

	return 0
}

install_unison() {
	cd /usr/ports/net/unison/

	install -vs work/unison-*/unison $FREENAS/usr/local/bin

	return 0
}
