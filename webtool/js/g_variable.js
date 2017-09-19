//------------------------------------------------------------------------------
// set javascript env
var g_variable = new Array();

var dir = document.location.pathname.split("/");
delete dir[(dir.length-1)];
dir = dir.join("/");

g_variable["_POST_URL_"] 	= "api/main.php";
g_variable["img_loader"] 	= "/imgs/loader-small.gif";
g_variable["gif_loader"] 	= "<img src='"+g_variable["img_loader"]+"' />";

g_variable["corpus_list_ck"] = null;
g_variable["corpus_list_ek"] = null;
g_variable["corpus_list_kc"] = null;
g_variable["corpus_list_ke"] = null;

g_variable["save_login_pane"] = null;
//------------------------------------------------------------------------------
