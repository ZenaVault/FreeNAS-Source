PORTNAME=			sipcalc
PORTVERSION=	1.1.5
CATEGORIES=		net-mgmt ipv6
COMMENT=			Sipcalc is an IP subnet calculator with IPv6 support
MAINTAINER=		ma.zoon@quicknet.nl

MASTER_SITES=	http://www.routemeister.net/projects/sipcalc/files/

USE_GETOPT_LONG=	yes
GNU_CONFIGURE=		yes

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/sipcalc ${FREENAS_ROOTFS}/usr/local/bin

.include <bsd.port.mk>
