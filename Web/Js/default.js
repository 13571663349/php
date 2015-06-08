function $(selector) {
	prefix = selector.charAt(0);
	if (prefix == '#') {
		return document.getElementById(selector.substr(1));
	}else if (prefix == '.') {
		return document.getElementsByClassName(selector.substr(1));
	}else{
		elements = document.getElementsByName(selector).length>0 ? document.getElementsByName(selector).length :
					document.getElementsByTagName(selector);

		if (elements.length){
			return elements.length > 1 ? elements : elements.item(0);
		}else{
			return null;
		}
	}
}