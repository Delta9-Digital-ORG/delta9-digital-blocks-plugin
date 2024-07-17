
export class Cookies {
	setCookie(name, value, hours) {
		let expires = "";
		
		if (hours) {
			let date = new Date();
			
			date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
			expires = "; expires=" + date.toUTCString();
		}
		
		document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	
	getCookie(name) {
		let nameEQ = name + "=";
		let cookieArray = document.cookie.split(';');
		
		for(var i = 0; i < cookieArray.length; i++) {
			let attribute = cookieArray[i];
			
			while (attribute.charAt(0)==' ') {
				attribute = attribute.substring(1, attribute.length);
			}
			
			if (attribute.indexOf(nameEQ) == 0) {
				let attributeValue = attribute.substring(nameEQ.length, attribute.length);
				
				return attributeValue;
			}
		}
		
		return null;
	}
	
	eraseCookie(name) {   
		document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
	}
}