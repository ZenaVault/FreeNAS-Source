PORTNAME=				bash
PATCHLEVEL=			39
PORTVERSION=		3.2.${PATCHLEVEL:S/^0//g}
PORTREVISION?=	1
CATEGORIES=			shells
COMMENT=				The GNU Project's Bourne Again SHell
MAINTAINER=			votdev@gmx.de


MASTER_SITES=				${MASTER_SITE_GNU:S/$/:bash/} \
										ftp://ftp.cwru.edu/pub/%SUBDIR%/:faq
MASTER_SITE_SUBDIR=	${PORTNAME}/:bash,faq
DISTNAME=						${PORTNAME}-${PORTVERSION:R}
DISTFILES=					${DISTNAME}${EXTRACT_SUFX}:bash
DIST_SUBDIR=				${PORTNAME}
EXTRACT_ONLY=				${DISTNAME}${EXTRACT_SUFX}

PATCH_SITES=				${MASTER_SITE_GNU} \
										ftp://ftp.cwru.edu/pub/%SUBDIR%/
PATCH_SITE_SUBDIR=	${PORTNAME}/${DISTNAME}-patches/
PATCHFILES!=				/usr/bin/jot -s " " -w \
										${PORTNAME}${PORTVERSION:R:S/.//g}-%03d \
										${PATCHLEVEL} 1 ${PATCHLEVEL}

EXTRA_PATCHES+=	${PATCHDIR}/xpatch-implicitcd
EXTRA_PATCHES+=	${PATCHDIR}/xpatch-colonbreakswords

GNU_CONFIGURE=	yes
USE_GETTEXT=		yes
CONFIGURE_ARGS=	--without-bash-malloc \
								--disable-rpath \
								--enable-disabled-builtins \
								--disable-help-builtin \
								--enable-static-link=no
CPPFLAGS+=			${PTHREAD_CFLAGS} \
								-I${LOCALBASE}/include
LDFLAGS+=				-L${LOCALBASE}/lib
CONFIGURE_ENV=	CPPFLAGS="${CPPFLAGS}" \
								LDFLAGS="${LDFLAGS}" \
								YACC="${YACC}"

.include <bsd.port.pre.mk>

post-patch:
	@${REINPLACE_CMD} -e "s|%%PREFIX%%|${PREFIX}|g" ${WRKSRC}/doc/bash.1

post-configure:
	@${FIND} ${WRKSRC} -name Makefile -print0 | ${XARGS} -0 \
		${REINPLACE_CMD} -e "s|^DESTDIR *=|& ${DESTDIR}|"

pre-build:
	@${ECHO_CMD} $$((${PORTREVISION}-1)) > ${WRKSRC}/.build

do-install:
	@${INSTALL_PROGRAM} -v ${WRKSRC}/bash ${FREENAS_ROOTFS}/usr/local/bin
	@${LN} -sfv /usr/local/bin/bash ${FREENAS_ROOTFS}/usr/local/bin/rbash

cklatest:
	-ncftpls \
	    ftp://ftp.cwru.edu/pub/${PORTNAME}/${PORTNAME}-${PORTVERSION:C/\.[0-9a-z]*$//}-patches/ \
		| ${TAIL}

.include <bsd.port.post.mk>