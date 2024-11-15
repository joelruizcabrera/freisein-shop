#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

set -euo pipefail

export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
export PROJECT_ROOT="${PROJECT_ROOT:-"$(dirname "$CWD")"}"
export NPM_CONFIG_FUND=false
export NPM_CONFIG_AUDIT=false
export NPM_CONFIG_UPDATE_NOTIFIER=false

if [[ -e "${PROJECT_ROOT}/vendor/shopware/platform" ]]; then
    STOREFRONT_ROOT="${STOREFRONT_ROOT:-"${PROJECT_ROOT}/vendor/shopware/platform/src/Storefront"}"
else
    STOREFRONT_ROOT="${STOREFRONT_ROOT:-"${PROJECT_ROOT}/vendor/shopware/storefront"}"
fi

BIN_TOOL="${CWD}/console"

if [[ ${CI:-""} ]]; then
    BIN_TOOL="${CWD}/ci"

    if [[ ! -x "$BIN_TOOL" ]]; then
        chmod +x "$BIN_TOOL"
    fi
fi

# build storefront
[[ ${SHOPWARE_SKIP_BUNDLE_DUMP:-""} ]] || "${BIN_TOOL}" hbh:bundle:single-bundle-dump Storefront "$@"
[[ ${SHOPWARE_SKIP_FEATURE_DUMP:-""} ]] || "${BIN_TOOL}" feature:dump

node "${STOREFRONT_ROOT}"/Resources/app/storefront/copy-to-vendor.js
npm --prefix "${STOREFRONT_ROOT}"/Resources/app/storefront run production

"${BIN_TOOL}" bundle:dump

[[ ${SHOPWARE_SKIP_ASSET_COPY:-""} ]] ||"${BIN_TOOL}" assets:install
[[ ${SHOPWARE_SKIP_THEME_COMPILE:-""} ]] || "${BIN_TOOL}" theme:compile --active-only

if ! [ "${1:-default}" = "--keep-cache" ]; then
    "${BIN_TOOL}" cache:clear
fi
