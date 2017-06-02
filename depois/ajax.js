import {loginVerifica} from './login.verifica.js'; //talvez comentar aqui

export function submitFormAjax(frm, div_id, fnCallback, checarLogin = true){
	const url = frm.action;
	const params = getParamsFromForm(frm);
	//const params = new FormData(frm);  //funciona sem especificar headers - Content-type
	ajaxFetch(url, params, fnCallback, div_id, checarLogin);
}

export function submitFetch(url, obj, div_id, fnCallback, checarLogin = true){
	const params = getParamsFromObject(obj);
	ajaxFetch(url, params, fnCallback, div_id, checarLogin);
}

export function submitInputFetch(url, el, div_id, fnCallback, checarLogin = true){
	const params = getParamsFromCustomDataAttributes(el);
	ajaxFetch(url, params, fnCallback, div_id, checarLogin);
}

function ajaxFetch(url, params, callbackFunction, div, checarLogin){
	fetch(url, {
		method: 'POST',
		//cache: 'no-store',
		credentials: 'same-origin', //necessário para enviar os cookies de sessão: PHPSESSID
		body: params,
		headers: {
			'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
		}
	}).then(response => {
		//console.log('response: ' + response);
		//console.log('Content-Type: ' + response.headers.get('Content-Type'));
		return response.json();
	}).then(json => {
		if(callbackFunction){
			if(checarLogin){
				loginVerifica(json, div, callbackFunction);
			}else{
				console.log('sem ir para login verifica');
				callbackFunction(json, div);
			}
		}else{
			alert('callbackFunction: ' + json[1]); //estranha este json[1]
		};
	}).catch(function(error){
		console.log('Exceção no ajaxFetch (olhe também no corpo da resposta - aba rede): ' + error);
	});
}

const getParamsFromForm = (frm) => {
	let params = '', qtdeElements = frm.elements.length;
	for(let i=0; i < qtdeElements; i += 1){
		let field = frm.elements[i];
		if(!field.hasAttribute("name")){continue;}
		//if(!field.hasAttribute("type")){continue;}
		//if(!field.type=='submit'){
			if(i>0){
				params += '&';
			}
			//params += field.name + '=' + field.value;
			params += field.name + '=' + encodeURIComponent(field.value);
		//}
	}
	return params;
}

const getParamsFromObject = (obj) => {
	let params = '', i = 0;
	for(let prop in obj){
		if(i>0){
			params += '&';
		}
		i += 1;
		params += prop + '=' + encodeURIComponent(obj[prop]);
	}
	return params;
}

const getParamsFromCustomDataAttributes = (el) => {
	let params = '', i = 0;
	let customAttr = Object.entries(el.dataset); //Object.entries() retorna um array cujos elementos são também arrays correspondentes aos pares de propriedades [key, value] enumeráveis
	for (let [key, value] of customAttr) {
		if(i>0){
			params += '&';
		}
		i += 1;
		params += key + '=' + encodeURIComponent(value);
	}
	return params;
}

//sugestão do Felipe Nascimento de Moura
//for(item in el.dataset){
//		console.log(item, el.dataset[item])
//}