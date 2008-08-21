PORTNAME=				transmission
PORTVERSION=		1.32
PORTREVISION?=	0
CATEGORIES?=		net-p2p
COMMENT=				A fast and lightweight CLI bitTorrent client
MAINTAINER=			votdev@gmx.de

MASTER_SITES=		http://download.m0k.org/${PORTNAME}/files/

LIB_DEPENDS+=		curl.4:${PORTSDIR}/ftp/curl

USE_GNOME?=			gnometarget pkgconfig
USE_OPENSSL=		yes
USE_BZIP2=			yes
USE_GMAKE=			yes
USE_AUTOTOOLS=	autoconf:262:env aclocal:19:env automake:19:env libtool:15:env
GNU_CONFIGURE=	yes

EXTRA_PATCHES=	${PATCHDIR}/${PORTNAME}${PKGNAMESUFFIX}-configure.ac

general-patch:
	@${REINPLACE_CMD} -e 's|SUBDIRS = . sample.*|SUBDIRS = .|g' \
		${WRKSRC}/third-party/libevent/Makefile.*
	@${FIND} ${WRKSRC} -name Makefile.* | ${XARGS} ${REINPLACE_CMD} -e \
		's|$$(OPENSSL_CFLAGS)||g ; s|$$(OPENSSL_LIBS)||g ; \
		s|noinst_PROGRAMS = |noinst_PROGRAMS = #|g'

post-patch: general-patch

pre-configure:
	@(cd ${WRKSRC} && ${SETENV} ${AUTOTOOLS_ENV} ${AUTORECONF} -W none)
	@${REINPLACE_CMD} -e \
		's|CFLAGS="-g -Wall.*"|CFLAGS="${CFLAGS} -Wall -W"|g ; \
		s|CXXFLAGS="-g -Wall.*"|CXXFLAGS="${CXXFLAGS} -Wall -W"|g ; \
		s|_pthread_flags=".*"|_pthread_flags="${PTHREAD_LIBS}"|g' \
			${WRKSRC}/configure
	@${REINPLACE_CMD} -e 's|-lrt||g' \
		${WRKSRC}/third-party/libevent/configure

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-daemon ${FREENAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/daemon/transmission-remote ${FREENAS_ROOTFS}/usr/local/bin

	@${MKDIR} -pv ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${MKDIR} -pv ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${MKDIR} -pv ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/progress
	@${MKDIR} -pv ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${MKDIR} -pv ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/stylesheets

	@${INSTALL_DATA} -v ${WRKSRC}/web/LICENSE ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/buttons/info_activity.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/buttons/info_general.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/buttons/tab_backgrounds.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/buttons/toolbar_buttons.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/buttons/torrent_buttons.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/buttons
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/favicon.ico ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/browser_firefox.gif ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/browser_opera.gif ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/browser_safari.gif ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/chrome.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/filter_bar.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/iphone_chrome.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/logo.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/graphics/transfer_arrows.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/graphics
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/progress/progress.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images/progress
	@${INSTALL_DATA} -v ${WRKSRC}/web/images/webclip-icon.png ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/index.html ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/common.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/dialog.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery-dimensions.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.contextmenu.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.form.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/jquery.transmenu.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/jquery/json.min.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript/jquery
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/menu.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/torrent.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/transmission.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/javascript/transmission.remote.js ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/javascript
	@${INSTALL_DATA} -v ${WRKSRC}/web/stylesheets/common.css ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/stylesheets
	@${INSTALL_DATA} -v ${WRKSRC}/web/stylesheets/iphone.css ${FREENAS_ROOTFS}/usr/local/share/${PORTNAME}/web/stylesheets

.include <bsd.port.mk>