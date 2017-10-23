function copyToClipboard(element) {
	alert("Gekopieerd naar uw klembord");
	var $temp = $("<input>");
	$("body").append($temp);
	$temp.val($(element).text()).select();
	document.execCommand("copy");
	$temp.remove();
}