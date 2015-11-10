
##############DCMD配置################
DCMD_APP_NAME=ffan_xapi_activity
DCMD_TMPT_NAME=install
DCMD_POLL_PROD=ffan_xapi_activity_product
DCMD_POLL_SIT=ffan_xapi_activity_sit
##########基础配置，开始，勿动################
ROOT=`pwd`
VERSION=`echo $RANDOM$RANDOM$RANDOM$RANDOM | md5sum | cut -d ' ' -f1`
PACK_VER=`date '+%Y%m%d%H%M%S'`
PACK_NAME="ffan_xapi_activity_$PACK_VER.tar.gz"
##############基础配置，结束，勿动################

#####################调试配置#####################
SHOW_CMD=false
#SHOW_CMD=true

#############源文件目录，一般不用修改#############
JS_ROOT=$ROOT/static/javascript/
CSS_ROOT=$ROOT/xapi/css/
IMG_ROOT=$ROOT/xapi/images/
WEB_ROOT=$ROOT/xapi/
SCRIPT_ROOT=$ROOT/scripts/

# LIB 以后作为独立模块，单独部署
BASE_LIB=$ROOT/deps/libs/

UTILS_ROOT=$ROOT/deps/utils/
COMMON_ROOT=$ROOT/common/

# DAO层不知道当初分离出的原因，以后考虑合入APP中，或者独立LIB中
DAO_ROOT=$ROOT/dao/

#############打包目录，一般不用修改#############
OP_JS_ROOT=$ROOT/output/bin/javascript/$VERSION/activity/
OP_CSS_ROOT=$ROOT/output/bin/css/$VERSION/
OP_IMG_ROOT=$ROOT/output/bin/images/
OP_WEB_ROOT=$ROOT/output/bin/php/activity/xapi/
OP_SCRIPT_ROOT=$ROOT/output/bin/scripts/
OP_BASE_LIB=$ROOT/output/bin/php/libs/
OP_UTILS_ROOT=$ROOT/output/bin/php/activity/utils/
OP_COMMON_ROOT=$ROOT/output/bin/php/activity/common/
OP_DAO_ROOT=$ROOT/output/bin/php/activity/dao/
