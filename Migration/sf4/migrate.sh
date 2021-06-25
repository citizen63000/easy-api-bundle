#!/bin/bash

REALDIR=`dirname $0`
PROJECTDIR="$(pwd)"
FILESDIR="${PROJECTDIR}/${REALDIR}/files"

#echo "${REALDIR}"
#echo "${FILESDIR}"
#echo "${PROJECTDIR}"

cp -R ${FILESDIR}/* ${PROJECTDIR}
mkdir -p ${PROJECTDIR}/src/Controller
mkdir -p ${PROJECTDIR}/src/Command
mkdir -p ${PROJECTDIR}/src/Entity
mkdir -p ${PROJECTDIR}/src/Services
mkdir -p ${PROJECTDIR}/src/Repository
mkdir -p ${PROJECTDIR}/src/Resources

#php "${REALDIR}/migrate.php"