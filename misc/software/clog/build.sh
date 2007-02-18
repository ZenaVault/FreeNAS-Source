#!/usr/bin/env bash

# Description displayed in dialog menu (max. 60 characters)
MENUDESC="clog - Circular log file tool"
# Is dialog menu selected [ ON | OFF ]
MENUSTATUS="ON"

build_clog() {
	cd /usr/src/usr.sbin

	tar -zxvf $SVNDIR/misc/software/clog/files/syslogd_clog-current.tgz
  tar -zxvf $SVNDIR/misc/software/clog/files/clog-1.0.1.tar.gz

  cd /usr/src/usr.sbin/syslogd
  make
  [ 0 != $? ] && return 1 # successful?

  cd /usr/src/usr.sbin/clog
  gcc clog.c -o clog

	return $?
}

install_clog() {
	cd /usr/src/usr.sbin/syslogd
  install -vs syslogd $FREENAS/usr/sbin

	cd /usr/src/usr.sbin/clog
  install -vs clog $FREENAS/usr/sbin	

	return 0
}
