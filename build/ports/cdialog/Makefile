PORTNAME=			cdialog
PORTVERSION=	1.1.20080819
PORTEPOCH=		1
CATEGORIES=		devel
COMMENT=			An enhanced version of 'dialog' to work with ncurses
MAINTAINER=		votdev@gmx.de

MASTER_SITES=	ftp://invisible-island.net/dialog/ \
							http://www.xs4all.nl/~tournoij/distfiles/ \
							http://www.rwxrwxrwx.net/distfiles/
DISTNAME=			${PORTNAME:S|^c||}-${PORTVERSION:R}-${PORTVERSION:E}

EXTRACT_SUFX=			.tgz
USE_AUTOTOOLS=		libtool:22
GNU_CONFIGURE=		yes
USE_LDCONFIG=			yes
CONFIGURE_ARGS=		--includedir=${PREFIX}/include/${PORTNAME} --with-libtool
MAKEFILE=					makefile

post-patch:
	@${FIND} ${WRKSRC}/samples/ -type f -exec ${REINPLACE_CMD} -i "" "s|dialog|cdialog|g" {} \;

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/dialog ${FREENAS_ROOTFS}/${PREFIX}/bin/cdialog
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/libdialog.so.* ${FREENAS_ROOTFS}/${PREFIX}/lib

.include <bsd.port.pre.mk>

.if ${OSVERSION} > 700032 || (${OSVERSION} < 700000 && ${OSVERSION} > 602106)
CONFIGURE_ARGS+=	--with-ncursesw --enable-widec
.else
CONFIGURE_ARGS+=	--with-ncurses
.endif

.include <bsd.port.post.mk>
