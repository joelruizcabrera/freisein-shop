#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

export PROJECT_ROOT="${PROJECT_ROOT:-"$(dirname "$CWD")"}"
export ENV_FILE=${ENV_FILE:-"${PROJECT_ROOT}/.env"}
export NPM_CONFIG_FUND=false
export NPM_CONFIG_AUDIT=false
export NPM_CONFIG_UPDATE_NOTIFIER=false

# shellcheck source=functions.sh
source "${PROJECT_ROOT}/bin/functions.sh"

curenv=$(declare -p -x)

load_dotenv "$ENV_FILE"

# Restore environment variables set globally
set -o allexport
eval "$curenv"
set +o allexport

set -euo pipefail

export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
export DISABLE_ADMIN_COMPILATION_TYPECHECK=true
export PROJECT_ROOT="${PROJECT_ROOT:-"$(dirname "$CWD")"}"

if [[ -e "${PROJECT_ROOT}/vendor/shopware/platform" ]]; then
    ADMIN_ROOT="${ADMIN_ROOT:-"${PROJECT_ROOT}/vendor/shopware/platform/src/Administration"}"
else
    ADMIN_ROOT="${ADMIN_ROOT:-"${PROJECT_ROOT}/vendor/shopware/administration"}"
fi

BIN_TOOL="${CWD}/console"

if [[ ${CI:-""} ]]; then
    BIN_TOOL="${CWD}/ci"

    if [[ ! -x "$BIN_TOOL" ]]; then
        chmod +x "$BIN_TOOL"
    fi
fi

# build admin
[[ ${SHOPWARE_SKIP_BUNDLE_DUMP:-""} ]] || "${BIN_TOOL}" hbh:bundle:single-bundle-dump $1

(cd "${ADMIN_ROOT}"/Resources/app/administration && npm run build)

"${BIN_TOOL}" bundle:dump

[[ ${SHOPWARE_SKIP_ASSET_COPY:-""} ]] ||"${BIN_TOOL}" assets:install
