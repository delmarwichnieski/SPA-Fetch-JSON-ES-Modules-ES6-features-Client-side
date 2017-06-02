import {divTopo} from './topo.js';
import {divRodape} from './rodape.js';
import {letraTurno} from './util/conversao.js';
import {mascara, mascNumero} from './util/mascara.js';
import {jqAlert} from './util/jquery.funcoes.js';
import {loadedSetEventClickListaDiarios} from './lista.diarios.loaded.js';
import {loadedSetEventClickAlterarSenha} from './lista.diarios.loadedsenha.js';
import {submitFormAjax, submitFetch} from './ajax.js';
import {processaSair} from './processa.sair.js';
import {diarioMenu} from './diario.menu.js';

export function estruturaPagina(json, strDivID){
	let strEstruturaPagina = `
		<div id="carregando"></div>
		<div id="div_jquery_alert" style="display:none;" title="Título do diálogo">Eu sou uma div diálogo</div>
		<div class="corpo">` + 
			divTopo(json.topo, json.ano_busca) + `
			<div id="centro" class="centro">` +
				strEstruturaListaDiarios(json) + `
			</div>` +
			divRodape() + `
		</div>`;

	document.body.className = 'bodypagina';
	document.body.innerHTML = strEstruturaPagina;

	listaDiarios(json, strDivID);

	loadedSetEventClickListaDiarios(strDivID, listaDiarios, json.ano_busca, true);

	loadedSetEventClickAlterarSenha(strDivID);

	loadedSair(json.topo.ambiente); //somente a primeira vez aqui na function estruturaPagina

	loadedBuscaDiarios(strDivID, listaDiarios);
}


export function estruturaListaDiarios(json, strDivID){
	document.getElementById('centro').innerHTML = strEstruturaListaDiarios(json);

	listaDiarios(json, strDivID);

	loadedSetEventClickListaDiarios(strDivID, listaDiarios, json.ano_busca, true);

	loadedSetEventClickAlterarSenha(strDivID);

	loadedBuscaDiarios(strDivID, listaDiarios);
}


function strEstruturaListaDiarios(json){
	let s = `
		<div class="mensagem_instrucoes">
			<a href="instrucoes_educacao_infantil.php">Instruções Educação Infantil</a>
			<a href="instrucoes_ensino_fundamental.php">Instruções Ensino Fundamental</a>
		</div>
		<div class="box_conteudo">
			<div id="buscaano">
				<form id="formano" name="form_busca" method="post" action="php/model/lista.diarios.php">
					<label>Ano Letivo:</label>
					<input type="text" name="ano" value="${json.ano_busca}" size="4" maxlength="4">
					<input type="submit" class="btn" value="Buscar">
					<input type="button" class="btn" value="<<" id="anomenos">
					<input type="button" class="btn" value=">>" id="anomais">
				</form>
			</div>
			<div id="div_saida"></div>
		</div>
		<div id="repetir" style="display:none"></div>`;
	return s;
}

function loadedBuscaDiarios(strDivID, listaDiarios){
	let anomais = document.getElementById("anomais");
	anomais.addEventListener('click', function(e){
		MaisMenosAno("+", this.form);
	});

	let anomenos = document.getElementById("anomenos");
	anomenos.addEventListener('click', function(e){
		MaisMenosAno("-", this.form);
	});

	function MaisMenosAno(operacao, frm){
		let ano = +frm.ano.value;
		if(operacao == "+"){
			ano += 1;
		} else if(operacao == "-"){
			ano -= 1;
		}
		frm.ano.value = ano;
		submitFormAjax(frm, strDivID, listaDiarios);
	}

	let frmAno = document.getElementById('formano');
	frmAno.ano.addEventListener('keypress', function(){
		mascara(this, mascNumero);
	});

	frmAno.addEventListener('submit', function(e){
		e.preventDefault(); //sempre que executar a função, tem que prevenir o envio default, pois significa que foi clicado o botão submit
		submitFormAjax(this, strDivID, listaDiarios);
	});
}

function loadedSair(ambiente){
	document.getElementById('topo_sair').addEventListener('click', function(e){
		e.preventDefault(); //sempre que executar a função, tem que prevenir o envio default, pois significa que foi clicado o botão submit
		let p = {ambiente: ambiente, motivoSair: -1};
		submitFetch('php/model/sair.php', p, 'div_erro', processaSair, false);
	});
}

function ajaxDiarioMenu(e){
	let ev = e || window.event; // cross-browser event
	let el = ev.target; 
	// if(event.stopPropagation){
		// event.stopPropagation()
	// }else{
		// event.cancelBubble = true
	// }

	let p = {
		cod_ministra: el.dataset.cod_ministra,
		semestre: el.dataset.semestre,
		suf: el.dataset.suf,
		numeronota: el.dataset.numeronota,
	};
	submitFetch('php/model/diario.menu.php', p, 'centro', diarioMenu);
}

function menuBotao(qtdebimtrim, cod_ministra, semestre, suf){
	if(qtdebimtrim[0]){
		if((cod_ministra) && (qtdebimtrim[1])){
			let r = `data-cod_ministra="${cod_ministra}" data-semestre="${semestre}" data-suf="${suf}" data-numeronota="${qtdebimtrim[1]}"`;
			r = `<input type="submit" value="Entrar" class="btn larg_70" ${r}>`;
			return r;
		}
	}else{
		return '<button class="btn_aviso larg_70" title="aviso">Aviso!</button>';
	}
}

function diarios_dlturno(json){
	let diarios = json.diarios_dlturno;
	let qtdeDiarios = diarios.length;
	let strDiarios = '';
	for(let i=0; i<qtdeDiarios; i++){
		strDiarios += `
			<tr class="tr_dados">
				<td class="ta_c">${diarios[i].CODIGO}</td>
				<td class="ta_c">${diarios[i].ANO}</td>
				<td>${diarios[i].CURSO}</td>
				<td>${diarios[i].SERIE}</td>
				<td class="ta_c">` + letraTurno(diarios[i].TURNO) + `</td>
				<td class="ta_c">${diarios[i].TURMA}</td>
				<td colspan="2" class="ta_l">${diarios[i].TIPO}</td>
				<td class="ta_l">` + menuBotao(diarios[i].QTDEBIMTRIM, diarios[i].CODIGO, 0, 'DLTURMA') + `</td>
			</tr>`;
	}
	return strDiarios;
}

function diarios(json){
	let diarios = json.diarios;
	let qtdeDiarios = diarios.length;
	let strDiarios = '';
	for(let i=0; i<qtdeDiarios; i++){
		strDiarios += `
			<tr class="tr_dados">
				<td class="ta_c">${diarios[i].CODIGO}</td>
				<td class="ta_c">${diarios[i].ANO}</td>
				<td>${diarios[i].CURSO}</td>
				<td>${diarios[i].SERIE}</td>
				<td class="ta_c">` + letraTurno(diarios[i].TURNO) + `</td>
				<td class="ta_c">`;
					if(diarios[i].QTDEBIMTRIM[0]){
						if(json.tipologin == "P"){
							//strDiarios += `<button class="btn" onclick="selDisc(${diarios[i].COD_CURSO}, ${diarios[i].COD_SERIE}, ${diarios[i].COD_TURMA}, ${json.ano_busca}, ${diarios[i].SEMESTRE}, ${diarios[i].QTDEBIMTRIM[0]})">${diarios[i].TURMA}</button>`;
							strDiarios += `<button class="btn">${diarios[i].TURMA}</button>`;
						}else{
							strDiarios += diarios[i].TURMA;
						}
					}else{
						strDiarios += `<button class="btn_aviso" title="aviso">${diarios[i].TURMA}</button>`; 
					}
					strDiarios += `
				</td>
				<td colspan="2">${diarios[i].DISCIPLINA}</td>
				<td class="ta_l">` + menuBotao(diarios[i].QTDEBIMTRIM, diarios[i].CODIGO, 0, '') + `</td>
			</tr>`;
	}
	return strDiarios;
}

function diarios_eja(json){
	let diarios = json.diarios_eja;
	let qtdeDiarios = diarios.length;
	let strDiarios = '';
	for(let i=0; i<qtdeDiarios; i++){
		strDiarios += `
			<tr class="tr_dados">
				<td class="ta_c">${diarios[i].CODIGO}</td>
				<td class="ta_c">${diarios[i].ANO}</td>
				<td>${diarios[i].CURSO}</td>
				<td>${diarios[i].SERIE}</td>
				<td class="ta_c">` + letraTurno(diarios[i].TURNO) + `</td>
				<td class="ta_c">`;
					if(diarios[i].QTDEBIMTRIM[0]){
						if(json.tipologin == "P"){
							//strDiarios += `<button class="btn" onclick="selDisc(${diarios[i].COD_CURSO}, ${diarios[i].COD_SERIE}, ${diarios[i].COD_TURMA}, ${json.ano_busca}, ${diarios[i].SEMESTRE}, ${diarios[i].QTDEBIMTRIM[0]})">${diarios[i].TURMA}</button>`;
							strDiarios += `<button class="btn">${diarios[i].TURMA}</button>`;
						}else{
							strDiarios += diarios[i].TURMA;
						}
					}else{
						strDiarios += `<button class="btn_aviso" title="aviso">${diarios[i].TURMA}</button>`; 
					}
					strDiarios += `
				</td>
				<td>${diarios[i].DISCIPLINA}</td>
				<td style="width:70px;">${diarios[i].SEMESTRE}º Semestre</td>
				<td class="ta_l">` + menuBotao(diarios[i].QTDEBIMTRIM, diarios[i].CODIGO, diarios[i].SEMESTRE, 'EJA') + `</td>
			</tr>`;
	}
	return strDiarios;
}

function tabela(json){
	let strTabela = `
		<div class="resultados_box">
			<table style="border:0;width:980px;border-spacing:2px;">
				<tr class="tr_titulo">
					<td style="width:42px;">Cód.</td>
					<td style="width:42px;">Ano</td>
					<td style="width:288px;">Curso</td>
					<td style="width:97px;">Série</td>
					<td style="width:78px;">Turno</td>
					<td style="width:59px;">Turma</td>
					<td colspan="2">Disciplina/Docente</td>
					<td style="width:108px;">Menu</td>
				</tr>` + diarios_dlturno(json) + diarios(json) + diarios_eja(json) + `
			</table>
		</div>`;
	return strTabela;
}

function listaDiarios(json, strDivID){
	//console.log('listaDiarios: ' + json);
	let strListaDiarios = '';
	if(json.statusListaDiarios==1){
		strListaDiarios = `<div class="ta_c">Nenhum Registro Encontrado no ano de ${json.ano_busca}!</div>`;
		document.getElementById(strDivID).innerHTML = strListaDiarios;
	}else{
		strListaDiarios = tabela(json);
		document.getElementById(strDivID).innerHTML = strListaDiarios;

		$(document).ready(function(){
			$("table button[title='aviso']").on("click", function(){
				let titulo = 'AVISO';
				let msg = 'Configurações de intervalos de data dos bimestres/trimestres ou o método de avaliação estão pendentes!<br>Comunique ao/à Secretário(a) Escolar!';
				jqAlert('#div_jquery_alert', $, titulo, msg, 14); //no segundo parâmetro (ler $) estou passando a jquery como parâmetro
			});

			$("table input").on("click", ajaxDiarioMenu);
		}); 
	}
}