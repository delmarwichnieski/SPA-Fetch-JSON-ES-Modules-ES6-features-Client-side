<?php
	require('../includes/configuracoes.php');
	require('../includes/login.verifica.php');

	if(isset($_POST['ano'])){
		$ano_busca = $_POST['ano'];
		$topo = [
			'statusTopo' => 2,
			'ambiente' => $ambiente
		];
	}else{
		//$ano_busca = date('Y');
		$ano_busca = 2013;
		require('topo.php');
	}

	$codprofessor = $_SESSION['codprofessor']; //para não ir tantas vezes em $_SESSION; melhorar a leitura; diminuir tamanho código fonte
	$codunidadeescolar = $_SESSION['codunidadeescolar'];
	$tipologin = $_SESSION['tipo'];

	// _fundamental
	$busca_total = ibase_query($dbcon, 'SELECT COUNT(CODIGO) FROM MINISTRA WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca);
	$total = ibase_fetch_object($busca_total)->COUNT;
	ibase_free_result($busca_total);

	// _dl_turno
	$busca_total_dlturno = ibase_query($dbcon, 'SELECT COUNT(CODIGO) FROM MINISTRADLTURMA WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca);
	$total_dlturno = ibase_fetch_object($busca_total_dlturno)->COUNT;
	ibase_free_result($busca_total_dlturno);

	// _eja
	$busca_total_eja = ibase_query($dbcon, 'SELECT COUNT(CODIGO) FROM MINISTRAEJA WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca);
	$total_eja = ibase_fetch_object($busca_total_eja)->COUNT;
	ibase_free_result($busca_total_eja);

	if (!($total+$total_eja+$total_dlturno)){
		$json += [
			'statusListaDiarios' => 1, //Nenhum Registro Encontrado
			'topo' => $topo,
			'ano_busca' => $ano_busca
		];
		echo json_encode($json); //no casso de array associativo, gera como objeto
	}else{
		$json += [
			'statusListaDiarios' => 2,
			'topo' => $topo,
			'ano_busca' => $ano_busca,
			'tipologin' => $tipologin
		];
		//pega as configurações da tabela UNIDADEESCOLARCONFCURSOANO e guarda em um array para passar como parâmetro na chamada da função array_vereficaUnEscConfcursoanoNumeroNota
		$array_unid_esc_confcursoano = array_unid_esc_confcursoano($dbcon, $codunidadeescolar, $ano_busca);

		//INÍCIO LISTA OS DIÁRIOS DIAS LETIVOS/TURNO (REGENTE E DINAMIZADOR)
		$diarios_dlturno = [];
		if($total_dlturno){
			$busca_dlturno = ibase_query($dbcon,  'SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.TIPO, M.ANO, CURSO.CURSO, SERIE.SERIE, TURMA.TURNO, TURMA.TURMA
																FROM
																	(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_CURSO, TIPO
																	 FROM MINISTRADLTURMA
																	 WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca.') AS M
																LEFT OUTER JOIN TURMA ON M.COD_TURMA = TURMA.CODIGO
																LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
																LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
																ORDER BY SERIE.SERIE, TURMA.TURMA');

			//pega as configurações da tabela AVALIACAODLTURMA e guarda em um array para passar como parâmetro na chamada da função array_vereficaUnEscConfcursoanoNumeroNota
			$array_busca_numero_nota = array_busca_numero_nota($dbcon, $codunidadeescolar, $ano_busca, 'DLTURMA'); 

			while($obj_dlturno = ibase_fetch_object($busca_dlturno)){
				//guarda em um array na posição [0] true ou false. True para indicar que existe (a configuração da tabela UNIDADEESCOLARCONFCURSOANO, seus intervalos de bim/trim e a configuração da tabela AVALIACAO[DLTURMA|EJA]). Se existe guarda na posição [1] o Numero de Notas (bim/trim) senão guarda null
				//o array $array_vereficaUnEscConfcursoanoNumeroNota também é passado para a função formata_bimtrim que irá desenhar a quantidade de botões de bim/trim equivalentes ao número de notas
				$array_vereficaUnEscConfcursoanoNumeroNota = array_vereficaUnEscConfcursoanoNumeroNota($array_unid_esc_confcursoano, $array_busca_numero_nota, $obj_dlturno->COD_CURSO, $obj_dlturno->COD_SERIE, 0, 'DLTURMA');
				if(trim($obj_dlturno->TIPO) == 'D'){ //remover trim
					$tipo = 'DINAMIZADOR';
				}else if(trim($obj_dlturno->TIPO) == 'R'){ //remover trim
					$tipo = 'REGENTE';
				}
				$diarios_dlturno[] = [
					'CODIGO' => $obj_dlturno->CODIGO,
					'ANO' => $obj_dlturno->ANO,
					'SEMESTRE' => 0,
					'CURSO' => $obj_dlturno->CURSO,
					'SERIE' => $obj_dlturno->SERIE,
					'TURMA' => $obj_dlturno->TURMA,
					'TURNO' => $obj_dlturno->TURNO,
					'TIPO' => $tipo,
					'COD_CURSO' => $obj_dlturno->COD_CURSO,
					'COD_SERIE' => $obj_dlturno->COD_SERIE,
					'COD_TURMA' => $obj_dlturno->COD_TURMA,
					'QTDEBIMTRIM' => $array_vereficaUnEscConfcursoanoNumeroNota
				];
			}
			ibase_free_result($busca_dlturno);
		}
		$json += ['diarios_dlturno' => $diarios_dlturno];
		//FIM LISTA OS DIÁRIOS DIAS LETIVOS/TURNO (REGENTE E DINAMIZADOR


		//INÍCIO LISTA OS DIÁRIOS AULA/DISCIPLINA
		$diarios = [];
		if($total){
			$busca = ibase_query($dbcon, 'SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.ANO, CURSO.CURSO, SERIE.SERIE, TURMA.TURNO, TURMA.TURMA, DISCIPLINA.DISCIPLINA
													FROM
														(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_DISCIPLINA, COD_CURSO
														 FROM MINISTRA
														 WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca.') AS M
													LEFT OUTER JOIN TURMA ON M.COD_TURMA = TURMA.CODIGO
													LEFT OUTER JOIN DISCIPLINA ON M.COD_DISCIPLINA = DISCIPLINA.CODIGO
													LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
													LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
													ORDER BY SERIE.SERIE, TURMA.TURMA, DISCIPLINA.DISCIPLINA');

			//pega as configurações da tabela AVALIACAO e guarda em um array para passar como parâmetro na chamada da função array_vereficaUnEscConfcursoanoNumeroNota
			$array_busca_numero_nota = array_busca_numero_nota($dbcon, $codunidadeescolar, $ano_busca, ''); 

			while($obj = ibase_fetch_object($busca)){
				//guarda em um array na posição [0] true ou false. True para indicar que existe (a configuração da tabela UNIDADEESCOLARCONFCURSOANO, seus intervalos de bim/trim e a configuração da tabela AVALIACAO[DLTURMA|EJA]). Se existe guarda na posição [1] o Numero de Notas (bim/trim) senão guarda null
				//se posição [0] é true é desenha o botão da turma para professor ou texto para Coordenador. se false botão de aviso
				//o array $array_vereficaUnEscConfcursoanoNumeroNota também é passado para a função formata_bimtrim que irá desenhar a quantidade de botões de bim/trim equivalentes ao número de notas
				$array_vereficaUnEscConfcursoanoNumeroNota = array_vereficaUnEscConfcursoanoNumeroNota($array_unid_esc_confcursoano, $array_busca_numero_nota, $obj->COD_CURSO, $obj->COD_SERIE, 0, '');
				$diarios[] = [
					'CODIGO' => $obj->CODIGO,
					'ANO' => $obj->ANO,
					'SEMESTRE' => 0,
					'CURSO' => $obj->CURSO,
					'SERIE' => $obj->SERIE,
					'TURMA' => $obj->TURMA,
					'DISCIPLINA' => $obj->DISCIPLINA,
					'TURNO' => $obj->TURNO,
					'COD_CURSO' => $obj->COD_CURSO,
					'COD_SERIE' => $obj->COD_SERIE,
					'COD_TURMA' => $obj->COD_TURMA,
					'QTDEBIMTRIM' => $array_vereficaUnEscConfcursoanoNumeroNota
				];
			}
			ibase_free_result($busca);
		}
		$json += ['diarios' => $diarios];
		//FIM LISTA OS DIÁRIOS AULA/DISCIPLINA


		//INÍCIO LISTA OS DIÁRIOS AULA/DISCIPLINA EJA
		$diarios_eja = [];
		if($total_eja){
			$busca_eja = ibase_query($dbcon, 'SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.ANO, M.SEMESTRE, CURSO.CURSO, SERIE.SERIE, TURMAEJA.TURNO, TURMAEJA.TURMAEJA, DISCIPLINA.DISCIPLINA
														 FROM
															(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_DISCIPLINA, COD_CURSO, SEMESTRE
															 FROM MINISTRAEJA
															 WHERE COD_PROFESSOR = '.$codprofessor.' AND COD_UNIDADEESCOLAR = '.$codunidadeescolar.' AND ANO = '.$ano_busca.') AS M
														 LEFT OUTER JOIN TURMAEJA ON M.COD_TURMA = TURMAEJA.CODIGO
														 LEFT OUTER JOIN DISCIPLINA ON M.COD_DISCIPLINA = DISCIPLINA.CODIGO
														 LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
														 LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
														 ORDER BY M.SEMESTRE, SERIE.SERIE, TURMAEJA.TURMAEJA, DISCIPLINA.DISCIPLINA');

			//pega as configurações da tabela AVALIACAOEJA e guarda em um array para passar como parâmetro na chamada da função array_vereficaUnEscConfcursoanoNumeroNota
			$array_busca_numero_nota = array_busca_numero_nota($dbcon, $codunidadeescolar, $ano_busca, 'EJA'); 

			while($obj_eja = ibase_fetch_object($busca_eja)){
				//guarda em um array na posição [0] true ou false. True para indicar que existe (a configuração da tabela UNIDADEESCOLARCONFCURSOANO, seus intervalos de bim/trim e a configuração da tabela AVALIACAO[DLTURMA|EJA]). Se existe guarda na posição [1] o Numero de Notas (bim/trim) senão guarda null
				//se posição [0] é true é desenha o botão da turma para professor ou texto para Coordenador. se false botão de aviso
				//o array $array_vereficaUnEscConfcursoanoNumeroNota também é passado para a função formata_bimtrim que irá desenhar a quantidade de botões de bim/trim equivalentes ao número de notas
				$array_vereficaUnEscConfcursoanoNumeroNota = array_vereficaUnEscConfcursoanoNumeroNota($array_unid_esc_confcursoano, $array_busca_numero_nota, $obj_eja->COD_CURSO, $obj_eja->COD_SERIE, $obj_eja->SEMESTRE, 'EJA');
				$diarios_eja[] = [
					'CODIGO' => $obj_eja->CODIGO,
					'ANO' => $obj_eja->ANO,
					'SEMESTRE' => trim($obj_eja->SEMESTRE), //esse trim poderá ser removido qdo o php parar de carregar espaços em branco em campos CHAR
					'CURSO' => $obj_eja->CURSO,
					'SERIE' => $obj_eja->SERIE,
					'TURMA' => $obj_eja->TURMAEJA,
					'DISCIPLINA' => $obj_eja->DISCIPLINA,
					'TURNO' => $obj_eja->TURNO,
					'COD_CURSO' => $obj_eja->COD_CURSO,
					'COD_SERIE' => $obj_eja->COD_SERIE,
					'COD_TURMA' => $obj_eja->COD_TURMA,
					'QTDEBIMTRIM' => $array_vereficaUnEscConfcursoanoNumeroNota
				];
			}
			ibase_free_result($busca_eja);
		}
		$json += ['diarios_eja' => $diarios_eja];
		//FIM LISTA OS DIÁRIOS AULA/DISCIPLINA EJA

		echo json_encode($json);
	}
?>