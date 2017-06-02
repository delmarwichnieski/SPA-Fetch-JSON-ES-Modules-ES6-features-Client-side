<?php
	require("includes/configuracoes.php");
	require("login.verifica.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php require("includes/cabecalho.php");?>
</head>
<body>
<div id="carregando"></div>
<div class="corpo">
	<?php require("includes/topo.php");?>
   
   <div class="centro">   
   
      <div class="mensagem_instrucoes">
      <a href="instrucoes_educacao_infantil.php">Instruções Educação Infantil</a>
      <a href="instrucoes_ensino_fundamental.php">Instruções Ensino Fundamental</a>
      </div>
      
   	<div class="box_conteudo">
      	<h1>Selecione um Diário:</h1>

         <div id="busca">
         	<form name="form_busca" method="get" action="">
            	<div>Utilize o filtro a baixo para aprimorar suas pesquisas:</div>
               <label>Ano Letivo:</label>
               <?php
						$ano_busca = $_GET["ano"];
						if(!isset($ano_busca) || !is_numeric($ano_busca)){
							$ano_busca = date("Y");
						}
					?>
               <input type="text" name="ano" value="<?php echo $ano_busca?>" size="4" maxlength="4" onkeypress="mascara(this,masc_numero)" />
               <input type="submit" value="Buscar" class="button" />
               <input type="button" value="<<" class="button" onclick="document.form_busca.ano.value = (eval(document.form_busca.ano.value) - 1);submit();" />
               <input type="button" value=">>" class="button" onclick="document.form_busca.ano.value = (eval(document.form_busca.ano.value) + 1);submit();" />
            </form>
         </div>

         <?php
				// _fundamental
				$busca_total = ibase_query("SELECT COUNT(CODIGO)
													 FROM MINISTRA
													 WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."'");
				$total = ibase_fetch_object($busca_total)->COUNT;
				ibase_free_result($busca_total);
				
				// _dl_turma
				$busca_total_dlturma = ibase_query("SELECT COUNT(CODIGO)
											 		         FROM MINISTRADLTURMA
													         WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."'");
																
				$total_dlturma = ibase_fetch_object($busca_total_dlturma)->COUNT;
				ibase_free_result($busca_total_dlturma);

				// _eja
				$busca_total_eja = ibase_query("SELECT COUNT(CODIGO)
														  FROM MINISTRAEJA
														  WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."'");
				$total_eja = ibase_fetch_object($busca_total_eja)->COUNT;
				ibase_free_result($busca_total_eja);

				if (!($total+$total_eja+$total_dlturma)){
			?>
         <div align="center">Nenhum Registro Encontrado!</div>
         <?php
				}else{
			?>
         <div class="resultados_box">
         	<table border="0" cellpadding="0" cellspacing="2" width="980">
            	<tr class="tr_titulo">
               	<td width="42">Cód.</td>
                  <td width="42">Ano</td>
                  <td width="288">Curso</td>
                  <td width="97">Série</td>
                  <td width="78">Turno</td>
                  <td width="59">Turma</td>
                  <td colspan="2">Disciplina/Docente</td>
                  <td width="108">Bim/Trim</td>
               </tr>
               <?php
						$tr_cor1 = "tr_cor1";
						$tr_cor2 = "tr_cor2";
						$tr_cor = $tr_cor1;

						if($total_dlturma){
							$busca_dlturma = ibase_query("SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.TIPO, M.ANO, CURSO.CURSO, SERIE.SERIE, TURMA.TURNO, TURMA.TURMA
														 FROM
														 	(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_CURSO, TIPO
															 FROM MINISTRADLTURMA
															 WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."') AS M
														 LEFT OUTER JOIN TURMA ON M.COD_TURMA = TURMA.CODIGO
														 LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
														 LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
														 ORDER BY SERIE.SERIE, TURMA.TURMA");

                  while($resultados_dlturma = ibase_fetch_object($busca_dlturma)){
                     if ($tr_cor == $tr_cor1) {$tr_cor = $tr_cor2;}else{$tr_cor = $tr_cor1;}
							$verefica_unidescconfcursoano_dlturma = verefica_unidescconfcursoano($_SESSION["codunidadeescolar".$session_nome], $resultados_dlturma->COD_CURSO, $resultados_dlturma->COD_SERIE, $resultados_dlturma->ANO, "DLTURMA", 0);
               ?>
            	<tr class="tr_dados <?php echo $tr_cor?>">
               	<td align="center"><?php echo $resultados_dlturma->CODIGO?></td>
                  <td align="center"><?php echo $resultados_dlturma->ANO?></td>
                  <td><?php echo $resultados_dlturma->CURSO?></td>
                  <td><?php echo $resultados_dlturma->SERIE?></td>
                  <td align="center"><?php echo letra_turno($resultados_dlturma->TURNO)?></td>
                  <td align="center"><?php echo $resultados_dlturma->TURMA?></td>
                  <td colspan="2" align="left"><?php if($resultados_dlturma->TIPO == "D"){echo "DINAMIZADOR";}else if($resultados_dlturma->TIPO == "R"){echo "REGENTE";}?></td>
                  <td align="left"><?php formata_bimtrim($verefica_unidescconfcursoano_dlturma, $_SESSION["codunidadeescolar".$session_nome], $resultados_dlturma->COD_SERIE, $ano_busca, $resultados_dlturma->CODIGO, "", "DLTURMA")?></td>
               </tr>
               <?php
						}
						ibase_free_result($busca_dlturma);
					}//if(total_dlturma)
					
					
						if($total){
							$busca = ibase_query("SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.ANO, CURSO.CURSO, SERIE.SERIE, TURMA.TURNO, TURMA.TURMA, DISCIPLINA.DISCIPLINA
														 FROM
														 	(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_DISCIPLINA, COD_CURSO
															 FROM MINISTRA
															 WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."') AS M
														 LEFT OUTER JOIN TURMA ON M.COD_TURMA = TURMA.CODIGO
														 LEFT OUTER JOIN DISCIPLINA ON M.COD_DISCIPLINA = DISCIPLINA.CODIGO
														 LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
														 LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
														 ORDER BY SERIE.SERIE, TURMA.TURMA, DISCIPLINA.DISCIPLINA");

                  while($resultados = ibase_fetch_object($busca)){
                     if ($tr_cor == $tr_cor1) {$tr_cor = $tr_cor2;}else{$tr_cor = $tr_cor1;}
							$verefica_unidescconfcursoano = verefica_unidescconfcursoano($_SESSION["codunidadeescolar".$session_nome], $resultados->COD_CURSO, $resultados->COD_SERIE, $resultados->ANO, "", 0);
               ?>
            	<tr class="tr_dados <?php echo $tr_cor?>">
               	<td align="center"><?php echo $resultados->CODIGO?></td>
                  <td align="center"><?php echo $resultados->ANO?></td>
                  <td><?php echo $resultados->CURSO?></td>
                  <td><?php echo $resultados->SERIE?></td>
                  <td align="center"><?php echo letra_turno($resultados->TURNO)?></td>
                  <td align="center">
                  	<?php if($verefica_unidescconfcursoano){?>
                  	<form name="form<?php echo $resultados->CODIGO?>" action="diario.multiplo.seleciona.php" method="post">
                        <input type="hidden" name="cod_curso" value="<?php echo $resultados->COD_CURSO?>" />
                        <input type="hidden" name="cod_serie" value="<?php echo $resultados->COD_SERIE?>" />
                        <input type="hidden" name="cod_turma" value="<?php echo $resultados->COD_TURMA?>" />
                        <input type="hidden" name="ano" value="<?php echo $ano_busca?>" />
                        <?php if($_SESSION["tipo".$session_nome] == "P"){?><input type="submit" class="button" value="<?php echo $resultados->TURMA?>" /><?php }else{echo $resultados->TURMA;}?>
                     </form>
                     <?php }else{?>
                     	<input type="button" class="button" value="<?php echo $resultados->TURMA?>" onclick="aviso(1);" />
                     <?php }?>
                  </td>
                  <td colspan="2"><?php echo $resultados->DISCIPLINA?></td>
                  <td align="left"><?php formata_bimtrim($verefica_unidescconfcursoano, $_SESSION["codunidadeescolar".$session_nome], $resultados->COD_SERIE, $ano_busca, $resultados->CODIGO, "", "")?></td>
               </tr>
               <?php
						}
						ibase_free_result($busca);
					}//if(total)

					if($total_eja){
						$busca_eja = ibase_query("SELECT M.CODIGO, M.COD_CURSO, M.COD_SERIE, M.COD_TURMA, M.ANO, M.SEMESTRE, CURSO.CURSO, SERIE.SERIE, TURMAEJA.TURNO, TURMAEJA.TURMAEJA, DISCIPLINA.DISCIPLINA
												 FROM
												 	(SELECT CODIGO, COD_SERIE, COD_TURMA, ANO, COD_DISCIPLINA, COD_CURSO, SEMESTRE
													FROM MINISTRAEJA
													WHERE COD_PROFESSOR = '".$_SESSION["codprofessor".$session_nome]."' AND COD_UNIDADEESCOLAR = '".$_SESSION["codunidadeescolar".$session_nome]."' AND ANO = '".$ano_busca."') AS M
												 LEFT OUTER JOIN TURMAEJA ON M.COD_TURMA = TURMAEJA.CODIGO
												 LEFT OUTER JOIN DISCIPLINA ON M.COD_DISCIPLINA = DISCIPLINA.CODIGO
												 LEFT OUTER JOIN CURSO ON M.COD_CURSO = CURSO.CODIGO
												 LEFT OUTER JOIN SERIE ON M.COD_SERIE = SERIE.CODIGO
												 ORDER BY SERIE.SERIE, TURMAEJA.TURMAEJA, M.SEMESTRE, DISCIPLINA.DISCIPLINA");
												 
                  while($resultados_eja = ibase_fetch_object($busca_eja)){
                     if($tr_cor == $tr_cor1){$tr_cor = $tr_cor2;}else{$tr_cor = $tr_cor1;}
							$verefica_unidescconfcursoano = verefica_unidescconfcursoano($_SESSION["codunidadeescolar".$session_nome], $resultados_eja->COD_CURSO, $resultados_eja->COD_SERIE, $resultados_eja->ANO, "EJA", $resultados_eja->SEMESTRE);
               ?>
            	<tr class="tr_dados <?php echo $tr_cor?>">
               	<td align="center"><?php echo $resultados_eja->CODIGO?></td>
                  <td align="center"><?php echo $resultados_eja->ANO?></td>
                  <td><?php echo $resultados_eja->CURSO?></td>
                  <td><?php echo $resultados_eja->SERIE?></td>
                  <td align="center"><?php echo letra_turno($resultados_eja->TURNO)?></td>
                  <td align="center">
                  	<?php if($verefica_unidescconfcursoano){?>
                  	<form name="form<?php echo $resultados_eja->CODIGO?>" action="diario.multiplo.seleciona.php" method="post">
                        <input type="hidden" name="cod_curso" value="<?php echo $resultados_eja->COD_CURSO?>" />
                        <input type="hidden" name="cod_serie" value="<?php echo $resultados_eja->COD_SERIE?>" />
                        <input type="hidden" name="cod_turma" value="<?php echo $resultados_eja->COD_TURMA?>" />
                        <input type="hidden" name="ano" value="<?php echo $ano_busca?>" />
                        <input type="hidden" name="semestre" value="<?php echo $resultados_eja->SEMESTRE?>" />
                        <input type="submit" class="button" value="<?php echo $resultados_eja->TURMAEJA?>" />
                     </form>
                     <?php }else{?>
                     	<input type="button" class="button" value="<?php echo $resultados_eja->TURMAEJA?>" onclick="aviso(1);" />
                     <?php }?>
                  </td>
                  <td><?php echo $resultados_eja->DISCIPLINA?></td>
                  <td width="70"><?php echo $resultados_eja->SEMESTRE?>&ordm; Semestre</td>
                  <td align="left"><?php formata_bimtrim($verefica_unidescconfcursoano, $_SESSION["codunidadeescolar".$session_nome], $resultados_eja->COD_SERIE, $ano_busca, $resultados_eja->CODIGO, $resultados_eja->SEMESTRE, "EJA")?></td>
               </tr>
               <?php
						}
						ibase_free_result($busca_eja);
					}//if(total_eja)
					?>
         </table>
         </div>
         <?php
				}
			?>
      </div>
   </div>
<?php require("includes/rodape.php");?>