

function remi(valor){
	var url = 'ColocarRemi.php';
	var pars = ("filtro=" + valor);
	var myAjax = new Ajax.Updater('divRemi', url, { method: 'get', parameters: pars});
}


