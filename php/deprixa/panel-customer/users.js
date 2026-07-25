function setUpdateAction() {
	if(confirm("Are you sure want to update all?")){
document.frmUser.action = "edit_user.php";
document.frmUser.submit();
}
}
function setDeleteAction() {
if(confirm("Are you sure want to delete these couriers?")) {
document.frmUser.action = "delete_user.php";
document.frmUser.submit();
}
}
function setUndoAction() {
if(confirm("Are you sure want to delete these Users?")) {
document.frmUser.action = "undo_user.php";
document.frmUser.submit();
}
}
