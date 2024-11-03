/*
 * validationJS v0.1.1
 * @desc Front End Validation
 * @author Mohd Fahmy Izwan Zulkhafri
 *
 * Usage:
 * 
$("#formUser").submit(function(event) {
	event.preventDefault();

	if (validateDataUser()) {
		alert('Submit Successfully');
	} else {

		let validateErr = validationJsError(); // get all error message (use only for option 1 & 2) / return raw message

		// option 1 : SINGLE message combine all error into single message
		let errMessage = [];
		for (let text in validateErr) {
		     errMessage.push("<li>" + validateErr[text] + "</li>");
		}

		toastr.error(errMessage);

		// option 2 : MULTI message. show message one by one
		for (let text in validateErr) {
		     toastr.error(validateErr[text]);
		}

		// option 3 : using toastr, 2nd param : single or multi. single is default
		- validationJsError('toastr'); <----  for single message
		- validationJsError('toastr', 'multi'); <---- for multiple message
	}
});

// rules function
function validateDataUser() {
 
	const rules = {
		'string_field': 'required|string|min:5|max:255',
		'numeric_field': 'required|numeric|min:3|max:15',
		'integer_field': 'required|integer|min:5|max:150',
		'email_field': 'required|email|min:5|max:15',
		'array_field': 'required|integer|array|min:4|max:15',
		'req_1_field': 'required|min:1|max:15',
		'req_2_field': 'required_if:req_1_field,=,2,3,4|max:15',
		'file_field': 'required|file|size:2|mimes:jpg,png',
 	}

	const message = {
		'string_field': 'String',
		'numeric_field': 'Numeric',
		'integer_field': 'Integer',
		'email_field': 'Email',
		'array_field': 'Array',
		'req_1_field': 'REQ 1',
		'req_2_field': 'REQ 2',
		'file_field': 'Upload File',
	};

	const custom_message = {
		'string_field': {
			'required': 'Ruangan String wajib diisi',
			'max': 'Maximun panjang ayat yang dibenarkan adalah 255',
		},
		'req_1_field': {
			'required': 'Ruangan REQ 1 wajib diisi',
			'min': 'Minimun yang dibenarkan adalah 1',
		},
	};

    return validationJs(rules, message, custom_message);
}

NOTES : 
1) custom_message in validationJs(rules, message, custom_message) is optional.
2) For "size" in file validation in MB only, if size not defined validation will use default value : 8 MB
3) For "mimes" in file validation, if mimes is not defined the validation will use the default value as it can refer to the function listFilesExt()

 *
 *  - 
 */

var _validationJSErrorMessage = []; // set default
var _validationJSresult = [];
var isInteger = false;

var traverseDOM = function (node, fn) {
	fn(node);
	node = node.firstChild;
	while (node) {
		traverseDOM(node, fn);
		node = node.nextSibling;
	}
}

var getElementByAttr = function (attr, val) {
	var _resultElem = [];
	traverseDOM(document.body, function (node) {
		var valid = node.nodeType === 1 && node.getAttribute(attr);
		if (typeof valid === 'string' && (valid === val || typeof val !== 'string')) {
			_resultElem.push(node);
		}
	});
	return _resultElem;
};

// VALIDATION SECTION

function validationJs(rules, message = null, customMessage = null, attrType = 'name') {

	_validationJSErrorMessage = []; // reset
	_validationJSresult = []; // reset

	for (let key in rules) {
		var id = key;
		var condition = rules[key];

		var arrayElem = isArrayRules(condition, attrType);
		var elemInput = arrayElem ? getElementByAttr(attrType, id + '[]') : getElementByAttr(attrType, id);

		for (let keyInput in elemInput) {
			if (typeof elemInput[keyInput] !== 'undefined') {

				var inputValue = elemInput[keyInput].value;
				var inputOriValue = elemInput[keyInput].value;

				var fieldName = (message != null && message.hasOwnProperty(id)) ? message[id] : id;

				if (arrayElem) {
					fieldName = fieldName + ' [' + (parseInt(keyInput) + 1) + ']';
				}

				var customText = (customMessage != null && customMessage.hasOwnProperty(id)) ? customMessage[id] : null;

				const conArr = condition.split("|").map(element => {
					return element.trim();
				});

				if (conArr.includes("file") || conArr.includes("files")) {

					var files = inputOriValue != '' ? {
						'name': elemInput[keyInput].files[0].name,
						'size': elemInput[keyInput].files[0].size,
						'type': elemInput[keyInput].files[0].type,
					} : null;

					validateUploadRules(conArr, fieldName, inputOriValue, customText, files, attrType);
				} else {
					for (let checkCon in conArr) {

						const newArr = conArr[checkCon].split(":").map(element => {
							return element.trim();
						});

						let count = Object.keys(newArr).length;

						if (count == 1) {
							if (newArr.includes("required")) {
								if (inputValue == null || inputValue == '')
									_validationJSresult['required'] = validationMessage('required', fieldName, null, customText);
							} else if (newArr.includes("email")) {
								if (inputValue != null && inputValue != '') {
									var lastAtPos = inputValue.lastIndexOf('@');
									var lastDotPos = inputValue.lastIndexOf('.');
									// var resultEmail = (lastAtPos < lastDotPos && lastAtPos > 0 && inputValue.indexOf('@@') == -1 && lastDotPos > 2 && (inputValue.length - lastDotPos) > 2);
									// (!resultEmail) ? _validationJSresult.push(validationMessage('email', fieldName, null, customText)): '';
									var resultEmail = (lastAtPos < lastDotPos && lastAtPos > 0 && inputValue.indexOf('@@') == -1 && lastDotPos > 2 && (inputValue.length - lastDotPos) > 2);
									(!resultEmail) ? _validationJSresult['email'] = validationMessage('email', fieldName, null, customText): '';
								}
							} else if (newArr.includes("integer")) {
								if (inputValue != null && inputValue != '') {

									if (containsAnyLetter(inputValue)) {
										_validationJSresult['integer'] = validationMessage('integer', fieldName, null, customText);
									} else {
										inputValue = Number(inputValue);
										if (typeof inputValue === 'number' && !Number.isNaN(inputValue) && Number.isInteger(inputValue)) {
											var remainder = (inputValue % 1);
											if (remainder !== 0) {
												_validationJSresult['integer'] = validationMessage('integer', fieldName, null, customText);

											}
										} else {
											_validationJSresult['integer'] = validationMessage('integer', fieldName, null, customText);
										}
									}
								}
							} else if (newArr.includes("numeric")) {
								if (inputValue != null && inputValue != '') {
									if (containsAnyLetter(inputValue)) {
										_validationJSresult['numeric'] = validationMessage('numeric', fieldName, null, customText);
									} else {
										inputValue = Number(inputValue);
										if (!IsNumeric(inputValue)) {
											_validationJSresult['numeric'] = validationMessage('numeric', fieldName, null, customText);
										}
									}
								}
							} else if (newArr.includes("string")) {
								if (inputValue != null && inputValue != '') {
									// var isString = (typeof inputValue === 'string' || inputValue instanceof String);
									// (!isString) ? _validationJSresult.push(validationMessage('string', fieldName, null, customText)): '';
									var isString = (typeof inputValue === 'string' || inputValue instanceof String);
									(!isString) ? _validationJSresult['string'] = validationMessage('string', fieldName, null, customText): '';
								}
							}
						} else {

							let conType = newArr[0];
							let conValue = newArr[1];

							if (newArr.includes("required_if")) {

								let conditionMeet = false;
								const reqIfCon = conValue.split(",").map(element => {
									return element.trim();
								});

								if (reqIfCon.length > 2) {
									const fieldNameReq = reqIfCon[0];
									const conditionReq = reqIfCon[1];

									if (typeof getElementByAttr(attrType, fieldNameReq)[0] !== 'undefined') {
										let fieldValueReq = getElementByAttr(attrType, fieldNameReq)[0].value;
										for (let conditionKey in reqIfCon) {
											if (fieldValueReq === 'null' || fieldValueReq === null || fieldValueReq == '') {
												conditionMeet = true;
											} else {
												if (conditionMeet !== true)
													conditionMeet = reqIfCon[conditionKey].toLowerCase() == fieldValueReq.toLowerCase() ? true : false;
											}
										}
									} else {
										alert("input " + attrType + "='" + fieldNameReq + "' not found.");
										console.log("input " + attrType + "='" + fieldNameReq + "' not found.");
									}
								}

								if (conditionMeet) {
									if (inputValue == null || inputValue == '')
										_validationJSresult['required'] = validationMessage('required', fieldName, null, customText);
								}

							} else {
								isInteger = conArr.includes("integer") ? true : false;
								validateConditionalRules(inputOriValue, fieldName, conType, conValue, customText, isInteger);
							}
						}
					}
				}

			} else {
				alert("input " + attrType + "='" + id + "' not found.");
				console.log("input " + attrType + "='" + id + "' not found.");
			}
		}
	}

	_validationJSErrorMessage = _validationJSresult;

	return Object.keys(_validationJSresult).length > 0 ? false : true;
}

function validateUploadRules(rulesArr, fieldName, inputValue, customText, filesInfo = null, attrType = 'name') {

	for (let key in rulesArr) {
		const newArr = rulesArr[key].split(":").map(element => {
			return element.trim();
		});

		let count = Object.keys(newArr).length;

		if (count > 1) {

			let conType = newArr[0];
			let conValue = newArr[1];

			if (newArr.includes("required_if")) {
				let conditionMeet = false;
				const reqIfCon = conValue.split(",").map(element => {
					return element.trim();
				});

				if (reqIfCon.length > 2) {
					const fieldNameReq = reqIfCon[0];
					const conditionReq = reqIfCon[1];

					if (typeof getElementByAttr(attrType, fieldNameReq)[0] !== 'undefined') {
						let fieldValueReq = getElementByAttr(attrType, fieldNameReq)[0].value;

						for (let conditionKey in reqIfCon) {
							if (conditionKey != 0 && conditionKey != 1) {
								if (fieldValueReq === 'null' || fieldValueReq === null || fieldValueReq == '') {
									conditionMeet = true;
								} else {
									if (conditionMeet !== true)
										conditionMeet = reqIfCon[conditionKey].toLowerCase() == fieldValueReq.toLowerCase() ? true : false;
								}
							}
						}
					} else {
						alert("input " + attrType + "='" + fieldNameReq + "' not found.");
						console.log("input " + attrType + "='" + fieldNameReq + "' not found.");
					}
				}

				if (conditionMeet) {
					if (inputValue == null || inputValue == '')
						_validationJSresult['required'] = validationMessage('required', fieldName, null, customText);
				}
			}
		}
	}

	if (rulesArr.includes("required")) {
		if (inputValue == null || inputValue == '')
			_validationJSresult['required'] = validationMessage('required', fieldName, null, customText);
	}

	if (inputValue != null && inputValue != '') {

		var defaultFileSize = 8;
		var inputExt = getExtensionFiles(inputValue);
		var oriSize = filesInfo['size'];
		var convertToMB = bytesToMbSize(oriSize, 4);

		for (let checkCon in rulesArr) {
			const newArr = rulesArr[checkCon].split(":").map(element => {
				return element.trim();
			});
			let count = Object.keys(newArr).length;
			let conType = newArr[0];
			let conValue = newArr[1];

			if (newArr.includes("mimes")) {

				newArr[1] = newArr[1].split(",").map(element => {
					return element.trim();
				});

				if (!newArr[1].includes(inputExt))
					_validationJSresult['mimes'] = validationMessage('mimes', fieldName, conValue, customText);

			} else if (!newArr.includes("mimes")) {
				if (!listFilesExt().includes(inputExt))
					_validationJSresult['mimes'] = validationMessage('mimes', fieldName, listFilesExt(), customText);
			}

			if (newArr.includes("size")) {
				if (parseFloat(convertToMB) > parseFloat(conValue))
					_validationJSresult['size'] = validationMessage('size', fieldName, parseFloat(conValue), customText);

			} else if (!newArr.includes("size")) {
				if (parseFloat(convertToMB) > parseFloat(defaultFileSize))
					_validationJSresult['size'] = validationMessage('size', fieldName, defaultFileSize, customText);
			}
		}

	}
}

function validateConditionalRules(inputOriValue, fieldName, conType, conValue, customText, isInteger = false) {

	if (!isInteger) {
		if (conType.includes("min_length") && inputOriValue != '') {
			if (inputOriValue.length < conValue)
				_validationJSresult['min_length'] = validationMessage('min_length', fieldName, conValue, customText);

		} else if (conType.includes("max_length") && inputOriValue != '') {
			if (inputOriValue.length > conValue)
				_validationJSresult['max_length'] = validationMessage('max_length', fieldName, conValue, customText);

		} else if (conType.includes("min") && inputOriValue != '') {
			if (inputOriValue.length < conValue)
				_validationJSresult['min'] = validationMessage('min_length', fieldName, conValue, customText);

		} else if (conType.includes("max") && inputOriValue != '') {
			if (inputOriValue.length > conValue)
				_validationJSresult['max'] = validationMessage('max_length', fieldName, conValue, customText);
		}
	} else {
		if (conType.includes("min_length") && inputOriValue != '') {
			if (inputOriValue.length < conValue)
				_validationJSresult['min_length'] = validationMessage('min_length', fieldName, conValue, customText);

		} else if (conType.includes("max_length") && inputOriValue != '') {
			if (inputOriValue.length > conValue)
				_validationJSresult['max_length'] = validationMessage('max_length', fieldName, conValue, customText);

		} else if (conType.includes("min") && inputOriValue != '') {
			if (parseFloat(inputOriValue) < parseFloat(conValue))
				_validationJSresult['min'] = validationMessage('min', fieldName, conValue, customText);

		} else if (conType.includes("max") && inputOriValue != '') {
			if (parseFloat(inputOriValue) > parseFloat(conValue))
				_validationJSresult['max'] = validationMessage('max', fieldName, conValue, customText);

		}
	}
}

function validateCheckScript(str) {
	const regex = /\<\s*script\s*\>/gi;

	if (regex.test(str)) {
		return true;
	}

	return false;
}

function validationMessage(type, fieldName, value = null, customMessage = null) {

	let message = null;

	let defaultMessage = {
		'required': 'The ' + fieldName + ' field is required.',
		'email': 'The ' + fieldName + ' must be a valid email address.',
		'min': 'The ' + fieldName + ' must be at least ' + value + '.',
		'max': 'The ' + fieldName + ' may not be greater than ' + value + '.',
		'min_length': 'The ' + fieldName + ' length must be at least ' + value + '.',
		'max_length': 'The ' + fieldName + ' length may not be greater than ' + value + '.',
		'integer': 'The ' + fieldName + ' must be an integer.',
		'numeric': 'The ' + fieldName + ' must be a number.',
		'string': 'The ' + fieldName + ' must be a string.',
		'array': 'The ' + fieldName + ' must be an array.',
		'file': 'The ' + fieldName + ' must be a file.',
		'image': 'The ' + fieldName + ' must be a image.',
		'size': 'The ' + fieldName + ' must not be greater than <b>' + value + ' MB </b>',
		'mimes': 'The ' + fieldName + ' must be a file of type: <b>' + value + '</b>',
		'mimetypes': 'The ' + fieldName + ' must be a file of type: <b>' + value + '</b>',
	}

	message = defaultMessage[type];

	if (customMessage != null && customMessage.hasOwnProperty(type)) {
		message = customMessage[type];
	}

	return message;
}

function validationJsError(type = 'raw', display = 'single') {

	if (type == 'raw') {
		return _validationJSErrorMessage;
	} else {

		// single display will show 1 error with multiple error message.
		if (type == 'toastr' && display == 'single') {
			let _validateJsErr = _validationJSErrorMessage; // get all error message
			let errMessage = [];
			for (let text in _validateJsErr) {
				errMessage.push("<li>" + _validateJsErr[text] + "</li>");
			}
			validationJsNotification(400, errMessage);
		}
		// multi display will show multiple error message.
		else if (type == 'toastr' && display == 'multi') {
			let _validateJsErr = _validationJSErrorMessage; // get all error message
			for (let text in _validateJsErr) {
				validationJsNotification(400, _validateJsErr[text]);
			}
		} else {
			return _validationJSErrorMessage;
		}
	}
}

function validationJsNotification(code = 200, text = 'Something went wrong') {
	const apiStatus = {
		200: 'OK',
		201: 'Created', // POST/PUT resulted in a new resource, MUST include Location header
		202: 'Accepted', // request accepted for processing but not yet completed, might be disallowed later
		204: 'No Content', // DELETE/PUT fulfilled, MUST NOT include message-body
		301: 'Moved Permanently', // The URL of the requested resource has been changed permanently
		304: 'Not Modified', // If-Modified-Since, MUST include Date header
		400: 'Bad Request', // malformed syntax
		401: 'Unauthorized', // Indicates that the request requires user authentication information. The client MAY repeat the request with a suitable Authorization header field
		403: 'Forbidden', // unauthorized
		404: 'Not Found', // request URI does not exist
		405: 'Method Not Allowed', // HTTP method unavailable for URI, MUST include Allow header
		415: 'Unsupported Media Type', // unacceptable request payload format for resource and/or method
		426: 'Upgrade Required',
		429: 'Too Many Requests',
		451: 'Unavailable For Legal Reasons', // REDACTED
		500: 'Internal Server Error', // all other errors
		501: 'Not Implemented', // (currently) unsupported request method
		503: 'Service Unavailable' // The server is not ready to handle the request.
	};

	var resCode = typeof code === 'number' ? code : code.status;
	var messageText = isSuccessValidationJS(resCode) ? ucfirst(text) + ' successfully' : isUnauthorizedValidationJS(resCode) ? 'Unauthorized: Access is denied' : isErrorValidationJS(resCode) ? text : 'Something went wrong';
	var type = (isSuccessValidationJS(code)) ? 'success' : 'error';
	var title = (isSuccessValidationJS(code)) ? 'Great!' : 'Ops!';

	// using toastr
	toastr.options = {
		"debug": false,
		"closeButton": !isMobileJs(),
		"newestOnTop": true,
		"progressBar": !isMobileJs(),
		"positionClass": !isMobileJs() ? "toast-top-right" : "toast-bottom-full-width",
		"preventDuplicates": isMobileJs(),
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	}

	Command: toastr[type](messageText, title)

}

// GENERAL FUNCTION SECTION

function isArrayRules(rules, attrType = null) {
	const conArr = rules.split("|");
	return conArr.includes("array") ? true : false;
}

function containsAnyLetter(str) {
	return /[a-zA-Z]/.test(str);
}

function getExtensionFiles(filename = null) {
	var ext = filename.split('.').pop();
	return (ext == filename) ? "" : ext;
}

function listFilesExt(typeExt = 'all') {

	let listExt = [];
	listExt['image'] = ['avif', 'bmp', 'btif', 'dwg', 'gif', 'ico', 'jpeg', 'jpg', 'pjpeg', 'pic', 'png', 'svg', 'tiff', 'webp'];
	listExt['audio'] = ['wma', 'mp3', 'wav'];
	listExt['document'] = ['doc', 'docx', 'ppt', 'pptx', 'csv', 'xls', 'xlsx', 'mpp', 'pdf', 'text'];
	listExt['video'] = ['3gp', 'mov', 'mp4', 'mkv', 'avi', 'm4v', 'wmv', 'ts', 'mpeg', 'ogv', 'webm', 'vcd', 'flv'];

	listExt['all'] = listExt['image'].concat(listExt['image'], listExt['audio'], listExt['document'], listExt['video']);
	return listExt[typeExt];
}

function IsNumeric(input) {
	var RE = /^-{0,1}\d*\.{0,1}\d+$/;
	return (RE.test(input));
}

function bytesToMbSize(bytes, roundTo = 2) {
	const converted = bytes / (1024 * 1024);
	return roundTo ? converted.toFixed(roundTo) : converted;
}

function isSuccessValidationJS(res) {
	const successStatus = [200, 201, 302];
	const status = typeof res === 'number' ? res : res.status;
	return successStatus.includes(status);
}

function isErrorValidationJS(res) {
	const errorStatus = [400, 404, 500, 422];
	const status = typeof res === 'number' ? res : res.status;
	return errorStatus.includes(status);
}

function isUnauthorizedValidationJS(res) {
	const unauthorizedStatus = [401, 403];
	const status = typeof res === 'number' ? res : res.status;
	return unauthorizedStatus.includes(status);
}
