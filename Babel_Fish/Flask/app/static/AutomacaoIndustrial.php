<?

header("Content-Type: text/html;  charset=utf-8",false);

include('../includes/conn_mysql.php');
include('../includes/conn_sap.php');
include('../includes/util.php');
include('../includes/autenticacao.php');
include('../includes/estrutura.php');
//include('../includes/email.class.php');
//include('../../extranet/includes/classes/email.class.php');
include_once('../includes/xajax.inc.php');
include_once('AutomacaoIndustrialAjax.php');



// Instancia o xajax
$xajax = new xajax($_SERVER['PHP_SELF']);
//$xajax->debugOn();
// Registra a função de validação de autenticação e acesso
$xajax->registerPreFunction(array('xajaxPreFunction', $auth, 'xajaxPreFunction'));
// Registra o objeto com os metodos ajax
$xajax->registerFunction("buscar_horarioMaquina");

$xajax->registerObject(new AutomacaoIndustrial());
// Processa as requisicoes
$xajax->processRequests();
// Imprime o cabecalho da pagina. Aqui dentro estao sendo impressos os JS do xajax automaticamente
cabecalho('AUTOMAÇÃO INDUSTRIAL', 'V', 'Principal.php');

if (isset($_POST['CodMaquina'])) {
	$descMaquina = sqlFirstCell("
		select
			DESC_MAQUINA
		from
			AUTOMACAO_CADASTRO_MAQUINA
		where
			COD_MAQUINA = ? AND
			inativa = 0
	", array($_POST['CodMaquina']));
	if ($descMaquina) {
		?>
		<div style="padding-bottom:5px;color:white;font-weight:bold;font-size:120%;">
			>>> Maquinário: <?=$descMaquina?>
			<span style="margin-left: 25px;"><a href='Sumarizacao.php?CodMaquina[]=<?=$_POST['CodMaquina']?>'>Visualização de Sumarização</a></span>
		</div>
		<?
	}
}

// busca os horarios da maquina seleiconada
function buscar_horarioMaquina($codMaquina){
	global $auth;
	
	$objResponse = new xajaxResponse();
	$sqlBuscaTurno = sqlFirstCell("SELECT id FROM turnos WHERE id_maquina = (SELECT id_maquinario FROM automacao_cadastro_maquina WHERE cod_maquina = ?) AND TIME(horario) <= TIME(NOW()) ORDER BY horario DESC LIMIT 1",array($codMaquina));
	if($sqlBuscaTurno != null)
	{	
		$sqlBuscaMaquina = sqlFirstCell("SELECT id_afericao FROM afericao_balanca WHERE DATE(DATA) = DATE(NOW()) AND TIME(DATA) >= (SELECT horario FROM turnos WHERE id_maquina = (SELECT id_maquinario FROM automacao_cadastro_maquina WHERE cod_maquina = ?) AND TIME(horario) <= TIME(NOW()) ORDER BY horario DESC LIMIT 1)",array($codMaquina));
		if($sqlBuscaMaquina == null)
		{
			$objResponse->addScript("var dialogWindow = new Dialog(300, 100); dialogWindow.setBody('<p style=\"font-size: 20px\">Por favor aferir a balança!</p>'); dialogWindow.okBtn.style.display = 'none'; dialogWindow.open();");	
			sqlCommand("INSERT INTO turnos_usuarios (idTurno, idUsuario) VALUES (?, ?)", array($sqlBuscaMaquina, $auth->usuario->idUsuario));	
		}
	}
	
	$objResponse->addScript("setTimeout(function(){xajax_buscar_horarioMaquina('".$codMaquina."'); }, 10000);");
	
	return $objResponse;
	
	
}

function busca_fichaTecnica($fichaTecnica) {

	//Url do Sharepoint
	$sharePointUrl = sqlFirstCell("SELECT valor FROM parametros WHERE descricao = ?", array('SharePointUrl'));
	$sharePointUser = sqlFirstCell("SELECT valor FROM parametros WHERE descricao = ?", array('SharePointUser'));
	//header("Location: learquivo.php?arquivo=".$sharePointUrl.$fichaTecnica);
	//$xajax->addRedirect($sharePointUrl.$fichaTecnica);
	//echo 'document.location.href="'.$sharePointUrl.$fichaTecnica.'";';
	//$sharePointUrl = 'http://172.16.22.26/qualidade/Documentos%20Compartilhados/';

	//Faz a conexão no sharePoint

	AcessarSharePoint( $sharePointUrl.$fichaTecnica , $sharePointUser );
	
	/*$ch = curl_init();
	if( !$ch ) {

		curl_setopt($ch, CURLOPT_URL, $sharePointUrl.$fichaTecnica);
		curl_setopt($ch, CURLOPT_USERPWD, $sharePointUser);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ch_handle = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

	} else {
		echo '<br><div style="padding:20px;">';
		echo '<span style="color:#ff0000">ERRO:</span> Não foi possível conectar no SharePoint. Por favor, comunicar ao administrador da Intranet.	';
		echo '</div>';
		echo '<div style="color:#ffffff">';
		echo curl_errno($ch).' - '.curl_error($ch);
		echo '</div>';

		//ENVIAR EMAIL
		//'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';
		global $auth;
		$corpo = utf8_decode('<html>'.
				 '<head></head>'.
				 '<body>'.
				 '<table cellpadding="2" cellspacing="0" border="0">'.
				 '<tr>'.
					'<td align="right"><strong>ERRO: </strong></td>'.
					'<td>Problema ao conectar no SharePoint.</td>'.
				 '</tr>'.
				 '<tr>'.
					'<td align="right">Endereço: </td>'.
					'<td>'.$_SERVER['HTTP_REFERER'].'</td>'.
				 '</tr>'.
				 '<tr>'.
					'<td align="right">Data/Hora: </td>'.
					'<td>'.date('d/m/Y G:i:s').'</td>'.
				 '</tr>'.
				 '<tr>'.
					'<td align="right">IP: </td>'.
					'<td>'.$_SERVER['REMOTE_ADDR'].'</td>'.
				 '</tr>'.
				 '<tr>'.
					'<td align="right">Usuário: </td>'.
					'<td>' . $auth->usuario->nomeSap . '</td>'.
				 '</tr>'.
				 '<tr>'.
					'<td align="right"><strong>Descrição do Erro:</strong></td>'.
					'<td>'.curl_errno($ch).' - '.curl_error($ch).'</td>'.
				 '</tr>'.
				 '</body>'.
				 '</html>');

		$email = new Email('samuel@x-neo.com.br', 'meincol@meincol.com.br', 'ERRO: Problema no SharePoint', $corpo);
		$email->enviar();

		exit;
	}*/
	//echo $info['http_code'].' - '.$sharePointUrl.$fichaTecnica.'<br />';

	//Caso o retorno do http for diferente de 404 - não encontrado ou 401 - Não Autenticado
	/*if($info['http_code'] != '404' && $info['http_code'] != '401')
		$res = $sharePointUrl.$fichaTecnica;
	else
		$res = '';

	return $res;*/
}
?>

<style type="text/css">
	button {
		font-size: 11px;
	}
	#CabecalhoOrdem {
		padding-left: 5px;
	}
	#CabecalhoOrdem table {
		border-collapse: separate;
		width: 850px;
	}
	#CabecalhoOrdem td {
		font-size: 12px;
		padding: 4px;
	}
	#CabecalhoOrdem td.valor {
		font-weight: bold;
		background-color: #f2f2f2;
	}
	#ItensOrdem th {
		text-align: left;
	}
	#ItensOrdem td {
		font-size: 11px;
	}
</style>
<script type="text/javascript" src="../js/dialog.js"></script>
<script type="text/javascript" language="Javascript" src="../js/util.js"></script>
<script type="text/javascript" language="Javascript" src="../js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" language="Javascript">
	var throb = new Throbber('Aguarde. Carregando...');
	function AssumirAmarrado(ordem) {
		//alert (ordem);
		xajax_assumirAmarrado(ordem);
	}
	function exibeAmarradoExtra(btn) {
		if (!confirm("Este comando irá cancelar os amarrados não impressos, deseja continuar?"))
			return;
		var pecasDiv = obj(btn.id.replace('Extra', 'Pecas'));
		var pecas = obj(btn.id.replace('Extra', 'PecasQtde'));
		pecas.value = "";
		pecasDiv.style.display = "inline";
		pecas.focus();
	}
	function exibePecas(btn) {
		var pecasDiv = obj(btn.id.replace('Finaliza', 'LancamentoPecas'));
		var pecas = obj(btn.id.replace('Finaliza', 'LancamentoPecasQtde'));
		pecas.value = "100";
		pecasDiv.style.display = "inline";
		pecas.focus();
	}
	function insereAmarradosPecas(evt, ordem) {
		var key = getKey(evt);
		var field = getTarget(evt);
		field.value = field.value.trim();
		if (key == 13) {
			if (field.value != '')
				xajax_inserirAmarradosPecas(ordem, field.value);
			stopEvent(evt);
		}
	}
	function insereAmarradoExtra(evt, ordem) {
		var key = getKey(evt);
		var field = getTarget(evt);
		field.value = field.value.trim();
		if (key == 13) {
			if (field.value != '')
				xajax_inserirAmarradoExtra(ordem, field.value);
			stopEvent(evt);
		}
	}

	function exibeAcompanhamento(ordem) {
			var row = obj('Acompanhamento' + ordem);
			if (row.style.display == 'none')
				xajax_acompanharProducao({ORDEM:ordem});
			else
				row.style.display = 'none';
	}

	function manutencaoParadas() {
		var form = obj('formRedirect');
		var action = form.action;
		form.action = 'ManutencaoParadas.php';
		form.submit();
		form.action = action;
	}

	function IsNumeric(sText) {
		var ValidChars = "0123456789.";
		var IsNumber = true;
		var Char;

		for (i = 0; i < sText.length && IsNumber == true; i++) {
			Char = sText.charAt(i);
			if (ValidChars.indexOf(Char) == -1) {
				IsNumber = false;
			}
		}
			return IsNumber;
	}


	var msg_dialog = null;
	function ver_msg(msg) {
		if (!msg_dialog) {
			msg_dialog = new Dialog(500, 150);
			msg_dialog.backgroundColor = '#FFF';
			msg_dialog.className = 'mensagens';
			msg_dialog.centralizeContents = false;
			msg_dialog.type = Dialog.TYPE_POPUP;
			msg_dialog.followScroll = true;
			msg_dialog.cancelCaption = 'Cancelar';
			msg_dialog.padding = 1;
		}
		msg_dialog.setBody(msg);
		msg_dialog.open();
	}

	var fichaTecnica = null;
	function exibeFichaTecnica(url) {
		//document.getElementById('formFichaTecnica').submit();
			//alert(url);
		if(url != '')
			fichaTecnica = window.open('lerarq.php?arquivo='+url, '');
		else
			alert('Não há Ficha Técnica Disponível.');
	}
	
	var ras = null;
	function exibeRAS(url) {
		//document.getElementById('formFichaTecnica').submit();
			//alert(url);
		if(url != '')
			ras = window.open('lerarq.php?arquivo='+url+'&ras=1', '');
		else
			alert('Não há RAS Disponível.');
	}

	var mapMontagem = null;
	function exibeMapaMontagem(url,centro) {
		//document.getElementById('formFichaTecnica').submit();
			//alert(url);
		if( centro == 'F01' || centro == '' ) {
			alert('Não foi encontrado nenhum mapa de montagem para esta máquina');
		} else {
			if(url != '')
				mapMontagem = window.open('lerarq.php?arquivo='+url, '');
			else
				alert('Não há Mapa de Montagem Disponível.');
		}
	}

	var mapImpeder = null;
	function exibeMapaImpeder(url,centro) {
		//document.getElementById('formFichaTecnica').submit();
			//alert(url);
		if( centro == 'F01' || centro == '' ) {
			alert('Não foi encontrado nenhum mapa impeder com retorno para esta máquina');
		} else {
			if(url != '')
				mapImpeder = window.open('lerarq.php?arquivo='+url, '');
			else
				alert('Não há Nenhum Mapa Impeder Disponível.');
		}
	}
	var observacao = null;
	function exibeObs(ordem) {
		xajax_exibeObs({'ORDEM':ordem});
	}
	function abre_obs(corpo) {
		/*
		var salvar = function() {
			xajax_salvarObservacao({'EMAIL': true, 'ORDEM': obj('obs_ordem').value, 'TEXTO': obj('observacao').value});
			removeEvent(observacao.cancelBtn, 'click', salvar);
		}
		*/
		if (!observacao) {
			observacao = new Dialog(300, 220);
			observacao.backgroundColor = '#FFF';
			observacao.centralizeContents = false;
			observacao.type = Dialog.TYPE_CHOICE;
			observacao.followScroll = true;
			observacao.okCaption = 'Gravar';
			observacao.okAction = function() {
				xajax_salvarObservacao({'ORDEM': obj('obs_ordem').value, 'TEXTO': obj('observacao').value});
			}
			observacao.cancelCaption = 'Fechar';
			observacao.padding = 1;
		}
		observacao.setBody(corpo);
		observacao.open();
	}

	var observacao = null;
	function exibeObs2(ordem) {
		xajax_exibeObs2({'ORDEM':ordem});
	}

	function abre_obs2(corpo,ordem,valor,codmaquina) {
		/*
		var salvar = function() {
			xajax_salvarObservacao({'EMAIL': true, 'ORDEM': obj('obs_ordem').value, 'TEXTO': obj('observacao').value});
			removeEvent(observacao.cancelBtn, 'click', salvar);
		}
		*/
		if (!observacao) {
			observacao = new Dialog(300, 220);
			observacao.backgroundColor = '#FFF';
			observacao.centralizeContents = false;
			observacao.onClose = function() {

			}
			observacao.type = Dialog.TYPE_CHOICE;
			observacao.followScroll = true;
			observacao.okCaption = 'Gravar';
			observacao.okAction = function() {

				if (obj('observacao').value == '') {
					alert('A observação não pode ficar em branco!');
				} else if (IsNumeric(obj('observacao').value) == false) {
					alert('Informe apenas o número da ordem!');
				} else {
					xajax_salvarObservacao2({'ORDEM': ordem, 'TEXTO': obj('observacao').value});
					xajax_alterarStatusMateriaNaoEncontrada({'ORDEM':ordem,'VALOR':valor,'CODMAQUINA':codmaquina});
				}

				/*if (confirm('Esta ordem de produção será finalizada, continuar?')) {
					if (obj('observacao').value == '') {
						alert('A observação não pode ficar em branco!');
						abre_obs2(corpo,ordem,valor,codmaquina);
					} else {

					}
				}*/

			}
			observacao.cancelCaption = 'Fechar';
			observacao.padding = 1;
		}
		observacao.setBody(corpo);
		observacao.open();



		/*
		//adiciona um novo evento ao botão cancelar
		addEvent(observacao.cancelBtn, 'click', salvar);
		//cria um novo botão
		var table = document.getElementsByTagName('table');
		var button = document.createElement('button');
		button.setAttribute('id', '_fechar_');
		button.setAttribute('type', 'button');
		button.style.cssText = 'margin-left:25px;width:75px;';
		button.innerHTML = 'Fechar';

		var fechar =  function() {
			var table = document.getElementsByTagName('table');
			table[table.length-1].childNodes[0].lastChild.childNodes[0].removeChild(document.getElementById('_fechar_'));
			observacao.close();
		}
		addEvent(button, 'click', fechar);
		table[table.length-1].childNodes[0].lastChild.childNodes[0].appendChild(button);
		*/
	}
	function verifica_caracteres(texto) {
		if(texto.length > 255) {
			alert('O campo Observação não pode ter mais que 255 caracteres!');
			texto = obj('observacao').value = texto.substr(0,255);
		}

		obj('caracter').innerHTML = 255-texto.length + ' caracteres restantes.';
	}

	function finalizar(ordem) {
		if(confirm("Finalizar a ordem "+ordem+" da máquina <?=$_POST['CodMaquina']?>?")) {
			xajax_finalizarProducao({'ORDEM': ordem, 'MAQUINA': '<?=$_POST['CodMaquina']?>', 'OBSERVACAO': ((obj('observacao')) ? obj('observacao').value : '')});
		}
	}

	function relatorioConfirmacaoComponentes() {
		var form = obj('formRedirect');
		var action = form.action;
		form.target = '_blank';
		form.action = '../ConfirmacaoComponente/RelatorioConfirmacaoComponente.php';
		form.submit();
		form.action = action;
	}
</script>
<table>
<form id="formRedirect" name="formRedirect" action="<?=$_SERVER['PHP_SELF']?>" method="POST" style="display:inline">
<input id="CodMaquina" name="CodMaquina" type="hidden" value="<?=$_POST['CodMaquina']?>" />
</form>
<form id="formOrdens"name="formOrdens" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
<input id="DATA_HORA_SETUP" name="DATA_HORA_SETUP" type="hidden" value="0"/>
<input id="CancelaAmarrados" name="CancelaAmarrados" type="hidden" value="0"/>
<table width="100%">
  <tr>
    <td>
		<?
		if (isset($_POST['CodMaquina'])) {	
			$OrdemProducao = null;
			$TotalQuantidade = 0;
			$FoundFicha = false;
			$sharePointUrl = sqlFirstCell("SELECT valor FROM parametros WHERE descricao = ?", array('SharePointUrl')); 
			//concat(material, '_', IF(CLIENTE != '', CLIENTE, CARACTERISTICA_CLIENTE),'.pdf') as fichaTecnica
			// verifica se existe ordem em produção
			$sql = "
				select
					*
				from
					AUTOMACAO_ORDENS
				where
					CENTRO_TRABALHO = '$_POST[CodMaquina]' and
					(STATUS = 'P' or STATUS = 'S')
				limit 1
			";
			$rs = mysql_query($sql);
			if (mysql_num_rows($rs) > 0) {
				$emProducao = true;
				$ordemData = mysql_fetch_array($rs, MYSQL_ASSOC);
				
				$FoundFicha = False;
				if ($ordemData['CNPJCPF'] == '') {
				
					$refFicha = sqlFirstCell("SELECT referencia FROM excecoes_ficha_tecnica WHERE cliente = ? AND material = ?", array($ordemData['CLIENTE'], $ordemData['MATERIAL']));

					if ($refFicha != '') {
						if ( file_exists((trim($sharePointUrl)).trim($ordemData['MATERIAL']).'_'.trim($refFicha).'.pdf') ) {
							$FoundFicha = True;
							$ordemData['fichaTecnica'] = (trim($sharePointUrl)).trim($ordemData['MATERIAL']).'_'.trim($refFicha).'.pdf';
						}
					}
					
				} else {
					if ( file_exists(trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($ordemData['CNPJCPF']).'.pdf') ) {
						$FoundFicha = True;
						$ordemData['fichaTecnica'] = trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($ordemData['CNPJCPF']).'.pdf';
					}
				}
				if (!$FoundFicha) {
					$ordemData['fichaTecnica'] = (trim($sharePointUrl)).$ordemData['CENTRO_TRABALHO'].'.pdf';
				}
				//echo  '<br> TOP  - '.$ordemData['fichaTecnica'].'<br> Centro '.$ordemData['CENTRO_TRABALHO'].'<br> Found '. (STRING)$FoundFicha .'<br> CNPJ '.$ordemData['CNPJCPF'] .'<br> Material '.$ordemData['MATERIAL'] .'<br> Ref '.trim($refFicha) .'<br> Cliente '.$ordemData['CLIENTE'].'<br> ';
								

				$sql_maq = "SELECT * FROM automacao_cadastro_maquina WHERE sem_pesagem = ? AND cod_maquina = ? AND inativa = 0 LIMIT 1";
				$m_params[] = 1;
				$m_params[] = $ordemData['CENTRO_TRABALHO'];

				$handle_maq = sqlCommand($sql_maq, $m_params);
				if ($handle_maq && mysql_num_rows($handle_maq) > 0) {
					$maquinaDT = 1;
				} else {
					$maquinaDT = '';
				}

				//$callFinaliza = xajaxCall('finalizarProducao', array(array('ORDEM' => $ordemData['ORDEM'], 'MAQUINA' => $_POST['CodMaquina'])), "Finalizar a ordem {$ordemData['ORDEM']} da máquina {$ordemData['CENTRO_TRABALHO']}?");
				$callAjuste = xajaxCall('ajusteMaquina', array(array('MAQUINA' => $_POST['CodMaquina'], 'ORDEM' => $ordemData['ORDEM'])), "Iniciar ajuste para a máquina {$descMaquina}?");
				$possuiAjuste = (bool)sqlFirstCell("select count(ID_PARADA_MAQUINA) from PARADA_MAQUINA where AJUSTE = 'X' and ORDEM = ? and DATA_HORA_FIM is null", array($ordemData['ORDEM']));
				/*$callAbortar = xajaxCall('abortarProducao', array(array('ORDEM' => $ordemData['ORDEM'], 'MAQUINA' => $_POST['CodMaquina'])), "Abortar a ordem {$ordemData['ORDEM']} da máquina {$ordemData['CENTRO_TRABALHO']}?");*/
				$OrdemProducao = $ordemData['ORDEM'];
				?>
				<table width="100%" cellpadding="0" cellspacing="0" border="0">
				  <tr>
				    <td width="70%" valign="top"><?=subtitle('Ordem em Produção/Setup', 'ordensProducao')?></td>
				    <td width="30%" valign="top" align="right"><?=button(array('name' => 'ManutencaoParadasProducao', 'type' => 'button', 'caption' => 'Manutenção de Paradas', 'onclick' => "\"manutencaoParadas();\""))?></td>
				  </tr>
				</table>
				<table width="100%" border="1" cellpadding="3" cellspacing="1">
				  <tr>
				    <th width="10%">OrdPro</th>
				    <th width="15%">Componente</th>
				    <th width="35%">Descrição Componente</th>
                    <th width="15%">Compr. Componente</th>
                    <th width="15%">Lote Componente</th>
				    <th width="10%">Posição</th>					
				  </tr>
				<?
				if ($ordemData['RELACAO_MATERIA'] == 1) {
					$rsMaterias = sqlCommand("
						select
							*
						from
							AUTOMACAO_MATERIA
						where
							ORDEM = ?
					", array($ordemData['ORDEM']));
					while ($materia = mysql_fetch_array($rsMaterias, MYSQL_ASSOC)) {
						$rsPosicao = sqlCommand("
							select
								ENDERECO, PESO
							from
								AUTOMACAO_AMARRADOS
							where
								LOTE_AMARRADO = ?
							LIMIT 1
						", array($ordemData['LOTE_COMPONENTE']));
						$posicao = ($rsPosicao ? mysql_fetch_array($rsPosicao, MYSQL_ASSOC) : null);
						?>
						  <tr>
						    <td><b><?=$materia['ORDEM']?></b></td>
						    <td><?=$materia['MATERIAL_COMPONENTE']?></td>
						    <td><?=$materia['DESCRICAO_COMPONENTE']?></td>
						    <td><?=$materia['COMPRIMENTO_COMPON']?></td>
						    <td><?=$materia['LOTE']?></td>
						    <td><?=($posicao ? $posicao['ENDERECO'] : '&nbsp;')?></td>							
						  </tr>
						<?
						if($ordemData['CENTRO_TRABALHO'] == 'BI01')
							$TotalQuantidade += convFloat($materia['QTD_PREVISTA'], 3);
					}
				} else {
					$rsPosicao = sqlCommand("
						select
							ENDERECO
						from
							AUTOMACAO_AMARRADOS
						where
							LOTE_AMARRADO = ?
						LIMIT 1
					", array($ordemData['LOTE_COMPONENTE']));
					$posicao = ($rsPosicao ? mysql_fetch_array($rsPosicao, MYSQL_ASSOC) : null);
					?>


					  <tr>
					    <td><b><?=$ordemData['ORDEM']?></b></td>
					    <td><?=$ordemData['MATERIAL_COMPONENTE']?></td>
					    <td><?=$ordemData['DESCRICAO_COMPONENTE']?></td>
                        <td><?=$ordemData['COMPRIMENTO_COMPON']?></td>
					    <td><?=$ordemData['LOTE_COMPONENTE']?></td>
					    <td><?=($posicao ? $posicao['ENDERECO'] : '&nbsp;')?></td>						
					  </tr>
					<?

				}
				?>
				</table>
				<?	/*$sql = "SELECT IF(SUM(peso) <> '', SUM(peso), '0.000') as qtd_conferida FROM automacao_ordens_confirmacao_componentes WHERE ordem = ? AND removido = ?";
					$handle = sqlCommand($sql, array($ordemData['ORDEM'], '0'));
					$row = mysql_fetch_array($handle, MYSQL_ASSOC);*/
				?>
				<table width="100%" border="1" cellpadding="3" cellspacing="1">
				  <tr>
				    <th>OV</th>
				    <th>Item OV</th>
				    <th>Cliente</th>
				    <th>Data Entrega</th>
				    <th>&nbsp;</th>
				  </tr>
				  <tr>
				    <td width="10%"><?=$ordemData['PEDIDO']?></td>
				    <td width="15%"><?=$ordemData['ITEM_PEDIDO']?></td>
				    <td width="35%"><b><?=$ordemData['CLIENTE']?></b></td>
				    <td width="15%">&nbsp;</td>
				    <td width="25%">&nbsp;</td>
				  </tr>
				</table>
				<table width="100%" border="1" cellpadding="3" cellspacing="1">
				  <tr>
				    <th>Material</th>
				    <th>Descrição</th>
				    <th>Comprimento</th>
				    <th>Lote</th>
				    <th>Quantidade</th>
					<!--<th>Qtd. Conferida</th>-->
				    <th>Nº Peças</th>
				    <th>Nº Amarrados</th>
				    <th>Peças p/ Amarrado</th>
				  </tr>
				  <tr>
				    <td width="10%"><?=$ordemData['MATERIAL']?></td>
				    <td width="30%"><?=$ordemData['DESCRICAO_MATERIAL']?></td>
				    <td width="10%"><?=$ordemData['COMPRIMENTO']?></td>
				    <td width="10%"><?=$ordemData['LOTE']?></td>
				    <td width="8%"><?=$ordemData['QTD_PREVISTA_PECAS']?></td>
					<!--<td width="7%"><?=$row['qtd_conferida']?></td>-->
				    <td width="8%"><?=($ordemData['UNIDADE'] == 'PC' ? intval($ordemData['QTD_PREVISTA_PECAS']) : intval($ordemData['NUMERO_PECAS_ORDEM']))?></td>
				    <td id="NumeroAmarrados<?=$ordemData['ORDEM']?>" width="9%"><?=($ordemData['UNIDADE'] == 'PC' ? '-' : $ordemData['NUMERO_AMARRADOS'])?></td>
					<td width="15%"><?
					if ($ordemData['UNIDADE'] != 'PC') {
						$pecasUltimo = sqlFirstCell("select PECAS_AMARRADO from AUTOMACAO_AMARRADOS where ORDEM = ? and NUM_AMARRADO_ORDEM = ?", array($ordemData['ORDEM'], $ordemData['NUMERO_AMARRADOS']));
						if ($pecasUltimo && $pecasUltimo < $ordemData['QTD_PECAS_AMARRADO'])
							print ($ordemData['NUMERO_AMARRADOS'] - 1) . 'AM ' . $ordemData['QTD_PECAS_AMARRADO'] . 'PC, 1AM ' . $pecasUltimo . 'PC';
						else
							print $ordemData['NUMERO_AMARRADOS'] . 'AM ' . $ordemData['QTD_PECAS_AMARRADO'] . 'PC';
					}
					?></td>
				  </tr>
				</table>
                <?
					$sql = "SELECT * FROM automacao_informacao WHERE ordem = ?";
					$resInfo = sqlCommand($sql, array($ordemData['ORDEM']));
					if($resInfo && mysql_num_rows($resInfo)) {
						$info = mysql_fetch_array($resInfo, MYSQL_ASSOC);
						if(mysql_num_rows($resInfo) != 1)
							mysql_data_seek($resInfo, 0);
					}
				?>
                <table width="100%" border="1" cellpadding="3" cellspacing="1">
				  <tr>
				    <th colspan="6">Aplicabilidade</th>
				  </tr>
   				  <tr>
				    <td colspan="6"><?=($info['aplic']) ? $info['aplic'] : '&nbsp;'?></td>
				  </tr>
                  <?
				  	$contador = 0;
					$colunas = 6;
				    while($linha = mysql_fetch_array($resInfo)) {
						$contador++;
						if($contador == 1) echo '<tr>';
						echo '<td style="padding:0;">'.
							    '<div style="color:#FFF;background:#666;text-align:center;font-weight:bold;padding:3px;">'.utf8_encode($linha['txt_carac']).'</div>'.
								'<div>'.$linha['vlr_carac'].'</div>'.
							 '</td>';
						if($contador == $colunas) { echo '</tr>'; $contador=0; }
					 }
					 if($contador != 0 && $contador < $colunas) {
						for($x=$contador;$x<$colunas;$x++) {
							echo '<td style="padding:0;">'.
								'<div style="color:#FFF;background:#666;text-align:center;font-weight:bold;padding:3px;">&nbsp;</div>'.
								'<div>&nbsp;</div>'.
							 '</td>';
						}
						echo '</tr>';
					 }
				  ?>
                </table>
				<table width="100%" border="0" cellpadding="3" cellspacing="0" style="margin-bottom:5px;">
				  <tr>
				    <td style="padding-left:5px;">
					<? $callIniciar = xajaxCall('iniciarProducao', array($ordemData));?>
					<? //=button(array('name'=>'Inicia' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Inicia Produção', 'disabled' => (($ordemData['STATUS'] != 'S') and ($row['qtd_conferida'] != $ordemData['QTD_PREVISTA_PECAS'])), 'onclick' => "\"{$callIniciar}\""))?>
					<? // 'disabled' => ($ordemData['STATUS'] != 'S'),  ?>
					<?=button(array('name'=>'Inicia' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Inicia Produção', 'disabled' => ($ordemData['STATUS'] != 'S'), 'onclick' => "\"{$callIniciar}\""))?>

					<? if ($ordemData['STATUS'] != 'S') { ?>

                    <!-- BOTÃO FINALIZAR ORDEM -->
						<?=button(array('name'=>'Finaliza' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Finaliza Produção', 'onclick' => "finalizar('{$ordemData['ORDEM']}')"))?>
					<!-- FIM BOTÃO FINALIZAR ORDEM -->

						<? //if ($ordemData['UNIDADE'] != 'PC' || $maquinaDT) { ?>

							<?=button(array('name'=>'AmarradoExtra' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Amarrado Extra', 'onclick' => "\"exibeAmarradoExtra(this);\""))?>
						<? //}?>
						<?=button(array('name'=>'Assumir' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Assumir', 'onclick' => "\"AssumirAmarrado(".$ordemData['ORDEM'].");\""))?>
					<? } ?>
					<?=button(array("name" => "RelatorioConfirmacaoComponentes", "caption" => "Relatório de Confirmação de Componentes", "type" => "button", "onclick" => "relatorioConfirmacaoComponentes();"))?>
					<div id="AmarradoPecas<?=$ordemData['ORDEM']?>" style="display:none">Número de Peças: <?=field(array('name'=>'AmarradoPecasQtde' . $ordemData['ORDEM'], 'type' => 'integer', 'caption' => 'Peças do Amarrado', 'size' => 15, 'maxlength' => 12, 'onkeypress' => 'insereAmarradoExtra(event, "' . $ordemData['ORDEM'] . '")'))?></div>
					<div id="LancamentoPecas<?=$ordemData['ORDEM']?>" style="display:none">Número de Peças por Amarrado: <?=field(array('name'=>'LancamentoPecasQtde' . $ordemData['ORDEM'], 'type' => 'integer', 'caption' => 'Peças por Amarrado', 'size' => 15, 'maxlength' => 12, 'onkeypress' => 'insereAmarradosPecas(event, "' . $ordemData['ORDEM'] . '")'))?></div>
					</td>
					<td align="right" style="padding-right:5px;">
					<? if ($ordemData['STATUS'] != 'S') { ?>
						<?//=button(array('name' => 'Ajuste' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Ajuste', 'onclick' => "\"{$callAjuste}\"", 'disabled' => $possuiAjuste))?>
						<?=button(array('name' => 'Acompanhar' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Acompanhamento', 'onclick' => "\"exibeAcompanhamento('{$ordemData['ORDEM']}')\""))?>
						<?/*button(array('name' => 'Abortar' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Abortar Ordem', 'onclick' => "\"{$callAbortar}\""))*/?>
                        <?=button(array('name' => 'Observacao' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Observação', 'onclick' => "\"exibeObs('{$ordemData['ORDEM']}')\""));?>
                        <?
						//echo (trim($sharePointUrl)).trim($ordemData['MATERIAL']).'_'.trim($ordemData['CLIENTE']).'.pdf';
                       /* if($ordemData['fichaTecnica'] != '') {
							$ordemData['fichaTecnica'] = busca_fichaTecnica($ordemData['fichaTecnica']); //Caso encontre busca pela ficha
						}
						//echo '<br>-1'.$ordemData['fichaTecnica'];
						if($ordemData['fichaTecnica'] == '') {
							$ordemData['fichaTecnica'] = busca_fichaTecnica($ordemData['MATERIAL'].'.pdf');
						}*/
						//echo '<br>-2'.$ordemData['fichaTecnica'];
						
							$FoundFicha = False;
							if ($ordemData['CNPJCPF'] == '') {
							
								$refFicha = sqlFirstCell("SELECT referencia FROM excecoes_ficha_tecnica WHERE cliente = ? AND material = ?", array($ordemData['CLIENTE'], $ordemData['MATERIAL']));

								if ($refFicha != '') {
									if ( file_exists(trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($refFicha).'.pdf') ) {
										$FoundFicha = True;
										$ordemData['fichaTecnica'] = trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($refFicha).'.pdf';
									}
								}
								
							} else {
								if ( file_exists(trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($ordemData['CNPJCPF']).'.pdf') ) {
									$FoundFicha = True;
									$ordemData['fichaTecnica'] = trim($sharePointUrl).trim($ordemData['MATERIAL']).'_'.trim($ordemData['CNPJCPF']).'.pdf';
								}
							}
							if (!$FoundFicha) {
								$ordemData['fichaTecnica'] = (trim($sharePointUrl)).$ordemData['CENTRO_TRABALHO'].'.pdf';
							}
							//echo  '<br> Middle  - '.$ordemData['fichaTecnica'].'<br> Centro '.$ordemData['CENTRO_TRABALHO'].'<br> Found '. (STRING)$FoundFicha .'<br> CNPJ '.$ordemData['CNPJCPF'] .'<br> Material '.$ordemData['MATERIAL'] .'<br> Ref '.trim($refFicha) .'<br> Cliente '.$ordemData['CLIENTE'].'<br> ';

							  if($_SERVER['HTTP_HOST']=='webteste.meincol.com.br'){
								$ordemData['fichaTecnica'] = str_replace('.29','.31',$ordemData['fichaTecnica']);
							  } 
						?>
                        <?=button(array('name' => 'fichaTecnica' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Ficha Técnica', 'title' => 'BANANAS', 'onclick' => "\"exibeFichaTecnica('{$ordemData['fichaTecnica']}')\""));?>
						
                        <? //button(array('name' => 'fichaTecnica' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Ficha Técnica', 'onclick' => "\"exibeFichaTecnica('{$ordemData['fichaTecnica']}')\""));?>
                        <?
						//---MAPA DE MONTAGEM
						$var_caminho_mm = sqlFirstCell("SELECT valor FROM parametros WHERE descricao = ?", array('CAMINHO_MAPA_MONTAGEM'));
						echo button(array('name' => 'motagem' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Mapa de Montagem', 'onclick' => "\"exibeMapaMontagem('" .addslashes(trim($var_caminho_mm) . trim($ordemData['CENTRO_TRABALHO']) . '_MM' . substr(trim($ordemData['CENTRO_TRABALHO']),(strlen(trim($ordemData['CENTRO_TRABALHO']))-2),strlen(trim($ordemData['CENTRO_TRABALHO'])))) . '.pdf' . "','" . trim($ordemData['CENTRO_TRABALHO']) . "')\""));
						//echo button(array('name' => 'motagem' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Mapa de Montagem', 'onclick' => "\"exibeMapaMontagem('" .trim($var_caminho_mm) . trim($ordemData['CENTRO_TRABALHO']) . '_MM' . substr(trim($ordemData['CENTRO_TRABALHO']),(strlen(trim($ordemData['CENTRO_TRABALHO']))-2),strlen(trim($ordemData['CENTRO_TRABALHO']))) . '.pdf' . "')\""));
						echo button(array('name' => 'motagem' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Mapa Impeder com Retorno', 'onclick' => "\"exibeMapaImpeder('" .addslashes(trim($var_caminho_mm) . 'MMIR_' . substr(trim($ordemData['CENTRO_TRABALHO']),(strlen(trim($ordemData['CENTRO_TRABALHO']))-3),strlen(trim($ordemData['CENTRO_TRABALHO'])))) . '.pdf' . "','" . trim($ordemData['CENTRO_TRABALHO']) . "')\""));
						?>
						<? //print_r($ordemData); ?>
						<? 
						$var_caminho_ras = sqlFirstCell("SELECT valor FROM parametros WHERE descricao = ?", array('CAMINHO_RAS'));
												
						$xras = $var_caminho_ras.$ordemData['CENTRO_TRABALHO'].'-'.$ordemData['MATERIAL'].'.pdf'; 
						if(file_exists($xras)){
							///EXISTE
						} else {
							$xras = '';
						}
						?>
						<?=button(array('name' => 'RAS' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'RAS', 'onclick' => "\"exibeRAS('".$xras."')\""));?>
					<? } ?>
					
    				</td>
				  </tr>
				  <tr id="Acompanhamento<?=$ordemData['ORDEM']?>" style="display:none"><td colspan="2"></td></tr>
				</table>
				<?

				if($ordemData['CENTRO_TRABALHO'] != 'BI01')
					$TotalQuantidade += convFloat($ordemData['QTD_PREVISTA'], 3);

			} else {
				$emProducao = false;
			}
			set_time_limit(100);


			session_start();
			if( trim($_REQUEST['periodoDias']) != '' ) {
				$_SESSION['periodoDias'] = $_REQUEST['periodoDias'];
			}


			//print_r($_REQUEST);
			if( trim($_REQUEST['periodoDias']) == '' ) {
				$rsOrdens = $sap->callFunction("ZF_PP_WEB1", array(
					array("IMPORT", "CENTRO_TRABALHO", $_POST['CodMaquina']),
					array("TABLE","LOG", array()),
					array("TABLE","ORDENS_PRODUCAO", array())
				));
			} else {
				$rsOrdens = $sap->callFunction("ZF_PP_WEB1", array(
					array("IMPORT", "CENTRO_TRABALHO", $_POST['CodMaquina']),
					array("IMPORT", "DIF_DIAS", $_SESSION['periodoDias']),
					array("TABLE","LOG", array()),
					array("TABLE","ORDENS_PRODUCAO", array())
				));
			}
			if ($sap->getStatus() == SAPRFC_OK && !empty($rsOrdens['ORDENS_PRODUCAO'])) {
				$cabecalhoAProduzir = false;
				$i = 1;
				$j = 0;
				while ($i<=sizeof($rsOrdens['ORDENS_PRODUCAO'])) {
					$dadosOrdemAtual = null;

					$ordemData = $rsOrdens['ORDENS_PRODUCAO'][$i];
					$ordemAtual = $ordemData['ORDEM'];

					// verifica se a ordem está no banco com status=W. se estiver, não é exibida
					$ordemEmFinalizacao = sqlFirstCell("select count(*) from AUTOMACAO_ORDENS where ORDEM = ? and (STATUS = 'W' or STATUS = 'A')", array($ordemAtual));

		 			if ($OrdemProducao != $ordemData['ORDEM'] && $ordemEmFinalizacao == 0)
		 			{
		 				$dadosOrdemAtual['LOTES'] = array();
		 				if (!$cabecalhoAProduzir) {
							$aProduzir = true;
							?>
							<table width="100%" cellpadding="0" cellspacing="0" border="0">
							  <tr>
							    <td width="70%" valign="top"><?=subtitle('Ordens a Produzir', 'ordensProduzir')?></td>
							    <td width="30%" valign="top" align="right"><?=button(array('name' => 'ManutencaoParadasProduzir', 'type' => 'button', 'caption' => 'Manutenção de Paradas', 'onclick' => "\"manutencaoParadas();\"", 'hidden' => true))?></td>
							  </tr>
							</table>
							<?
							$cabecalhoAProduzir = true;
		 				}
						// cabeçalho da ordem
						?>
						<table width="100%" border="1" cellpadding="3" cellspacing="1">
						  <tr>
						    <th width="10%">OrdPro</th>
						    <th width="15%">Componente</th>
					  	    <th width="35%">Descrição Componente</th>
						    <!--<th width="15%">Compr. Componente 1</th>-->
                            <?
								$sql_par = "SELECT valor FROM parametros WHERE descricao = 'MAQUINA_FORMADORA' ";
								$handle_par = mysql_query($sql_par);
								if ($handle_par && mysql_num_rows($handle_par) > 0) {
									$ds_par = mysql_fetch_array($handle_par);
									if( strpos($ds_par['valor'],$_POST['CodMaquina']) > 0 ) {
										//achou ocorrencia nos parametros
										$td4 = "Comprimento";
									} else {
										$td4 = "Compr. Componente";
									}
								} else {
									$td4 = "Compr. Componente";
								}

								/*
								if(substr(trim($_POST['CodMaquina']),0,1)=="F") {
									$td4 = "Comprimento";
								} else {
									$td4 = "Compr. Componente";
								}*/
							?>
							<th width="15%"><?=$td4?></th>
						    <th width="15%">Lote Componente</th>
						    <th width="10%">Posição</th>
						  </tr>
						  <?
							while (($ordemAtual == $ordemData['ORDEM']) && ($i <= sizeof($rsOrdens['ORDENS_PRODUCAO']))) {
								$rsPosicao = sqlCommand("select ENDERECO from AUTOMACAO_AMARRADOS where LOTE_AMARRADO = ? LIMIT 1", array($ordemData['LOTE_COMPONENTE']));
								$posicao = ($rsPosicao ? mysql_fetch_array($rsPosicao, MYSQL_ASSOC) : null);
								?>
								<tr>
							      <td><b><?=$ordemData['ORDEM']?></b></td>
							      <td><?=$ordemData['MATERIAL_COMPONENTE']?></td>
							      <td><?=$ordemData['DESCRICAO_COMPONENTE']?></td>
							      <!--<td><?=$ordemData['COMPRIMENTO_COMPON']?></td>-->
                                  <td><?
								  	$sql_par = "SELECT valor FROM parametros WHERE descricao = 'MAQUINA_FORMADORA' ";
									$handle_par = mysql_query($sql_par);
									if ($handle_par && mysql_num_rows($handle_par) > 0) {
										$ds_par = mysql_fetch_array($handle_par);
										if( strpos($ds_par['valor'],$_POST['CodMaquina']) > 0 ) {
											//achou ocorrencia nos parametros
											echo $ordemData['CARACTERISTICA_COMPRIMENTO'];
										} else {
											echo $ordemData['COMPRIMENTO_COMPON'];
										}
									} else {
										echo $ordemData['COMPRIMENTO_COMPON'];
									}

								/*	if(substr(trim($_POST['CodMaquina']),0,1)=="F") {
										echo $ordemData['CARACTERISTICA_COMPRIMENTO'];
									}else {
										echo $ordemData['COMPRIMENTO_COMPON'];
									}*/
								  ?></td>
							      <td><?=$ordemData['LOTE_COMPONENTE']?></td>
							      <td><?=($posicao ? $posicao['ENDERECO'] : '&nbsp;')?></td>
							    </tr>
								<?
								$dadosOrdemAtual = array_merge($dadosOrdemAtual, $ordemData);
								$dadosOrdemAtual['DATA_HORA_SETUP'] = 'F$DATA_HORA_SETUP';
								$dadosOrdemAtual['LOTES'][] = array('LOTE' => $ordemData['LOTE_COMPONENTE'], 'QTD' => $ordemData['QTDE_PREVISTA']);
								$i++;
								if ($i <= sizeof($rsOrdens['ORDENS_PRODUCAO'])) {
									$ordemData = $rsOrdens['ORDENS_PRODUCAO'][$i];
								}
							}
							$j++;
						  ?>
						</table>
                        <table width="100%" border="1" cellpadding="3" cellspacing="1">
                          <tr>
                            <th>OV</th>
                            <th>Item OV</th>
                            <th>Cliente</th>
                            <th>Data Entrega</th>
                            <th>&nbsp;</th>
                          </tr>
                          <tr>
                            <td width="10%"><?=$dadosOrdemAtual['PEDIDO']?></td>
                            <td width="15%"><?=$dadosOrdemAtual['ITEM_PEDIDO']?></td>
                            <td width="35%"><b><?=($dadosOrdemAtual['CLIENTE'] != '')?$dadosOrdemAtual['CLIENTE']:$dadosOrdemAtual['CARACTERISTICA_CLIENTE'];?></b></td>
                            <td width="15%">&nbsp;</td>
                            <td width="25%">&nbsp;</td>
                          </tr>
                        </table>
						<?	/*$sql = "SELECT IF(SUM(peso) <> '', SUM(peso), '0.000') as qtd_conferida FROM automacao_ordens_confirmacao_componentes WHERE ordem = ? AND removido = ?";
							$handle = sqlCommand($sql, array($dadosOrdemAtual['ORDEM'], '0'));
							$row = mysql_fetch_array($handle, MYSQL_ASSOC);*/
						?>
                        <table width="100%" border="1" cellpadding="3" cellspacing="1">
                          <tr>
                            <th>Material</th>
                            <th>Descrição</th>
                            <!--Comprim. Tubo Cortado
                            <th>Comprimento 2</th>-->
                            <?

								$sql_par = "SELECT valor FROM parametros WHERE descricao = 'MAQUINA_FORMADORA' ";
								$handle_par = mysql_query($sql_par);
								if ($handle_par && mysql_num_rows($handle_par) > 0) {
									$ds_par = mysql_fetch_array($handle_par);
									if( strpos($ds_par['valor'],$_POST['CodMaquina']) > 0 ) {
										//achou ocorrencia nos parametros
										$td4 = "Comprim. Tubo Cortado";
									} else {
										$td4 = "Comprimento";
									}
								} else {
									$td4 = "Comprimento";
								}

								/*if(substr(trim($_POST['CodMaquina']),0,1)=="F") {
									$td4 = "Comprim. Tubo Cortado";
								} else {
									$td4 = "Comprimento";
								}*/
							?>
							<th><?=$td4?></th>
                            <th>Lote</th>
                            <th>Quantidade</th>
							<!--<th>Qtd. Conferida</th>-->
                            <th>Nº Peças</th>
                            <th>Nº Amarrados</th>
                          </tr>
                          <tr>
                            <td width="10%"><?=$dadosOrdemAtual['MATERIAL']?></td>
                            <td width="30%"><?=$dadosOrdemAtual['DESCRICAO_MATERIAL']?></td>
                            <td width="10%"><?

								$sql_par = "SELECT valor FROM parametros WHERE descricao = 'MAQUINA_FORMADORA' ";
								$handle_par = mysql_query($sql_par);
								if ($handle_par && mysql_num_rows($handle_par) > 0) {
									$ds_par = mysql_fetch_array($handle_par);
									if( strpos($ds_par['valor'],$_POST['CodMaquina']) > 0 ) {
										//achou ocorrencia nos parametros
										echo $dadosOrdemAtual['CARACT_COMP_TUBO_CORTADO'];
									} else {
										echo $dadosOrdemAtual['CARACTERISTICA_COMPRIMENTO'];
									}
								} else {
									echo $dadosOrdemAtual['CARACTERISTICA_COMPRIMENTO'];
								}
							/*if(substr(trim($_POST['CodMaquina']),0,1)=="F") {
								echo $dadosOrdemAtual['CARACT_COMP_TUBO_CORTADO'];
							}else {
								echo $dadosOrdemAtual['CARACTERISTICA_COMPRIMENTO'];
							}*/
							?></td>
                            <td width="10%"><?=$dadosOrdemAtual['LOTE']?></td>
                            <td width="10%"><?=$dadosOrdemAtual['QTDE_TOTAL']?></td>
							<!--<td width="10%"><?=$row['qtd_conferida']?></td>-->
                            <td width="10%"><?=($dadosOrdemAtual['UMB'] == 'PC' ? intval($dadosOrdemAtual['QTDE_TOTAL']) : intval($dadosOrdemAtual['NUMERO_PECAS_ORDEM']))?></td>
                            <td id="NumeroAmarrados<?=$dadosOrdemAtual['ORDEM']?>" width="10%"><?=($dadosOrdemAtual['UMB'] == 'PC' ? '-' : $dadosOrdemAtual['NUMERO_AMARRADOS'])?></td>
                          </tr>
                        </table>
                        <div id="Aplicabilidade_<?=$dadosOrdemAtual['ORDEM']?>"></div>
						<?
						$TotalQuantidade += convFloat($dadosOrdemAtual['QTDE_TOTAL'], 3);
						if ($j == 1 && !$emProducao) {
							//$maquinaDT = ($dadosOrdemAtual['CENTRO_TRABALHO'] == 'DT01' || $dadosOrdemAtual['CENTRO_TRABALHO'] == 'DT02' || $dadosOrdemAtual['CENTRO_TRABALHO'] == 'BI02' || $ordemData['CENTRO_TRABALHO'] == 'UF01');

							$sql_maq = "SELECT * FROM automacao_cadastro_maquina WHERE sem_pesagem = ? AND cod_maquina = ? AND inativa = 0 LIMIT 1";
							$m_params[] = 1;
							$m_params[] = $dadosOrdemAtual['CENTRO_TRABALHO'];

							$handle_maq = sqlCommand($sql_maq, $m_params);
							if ($handle_maq && mysql_num_rows($handle_maq) > 0) {
								$maquinaDT = 1;
							} else {
								$maquinaDT = '';
							}

							$callIniciar = xajaxCall('iniciarProducao', array($dadosOrdemAtual));
							$callFinaliza = xajaxCall('finalizarProducao', array(array('ORDEM' => $dadosOrdemAtual['ORDEM'], 'MAQUINA' => $_POST['CodMaquina'])));
							/*$callAbortar = xajaxCall('abortarProducao', array(array('ORDEM' => $dadosOrdemAtual['ORDEM'], 'MAQUINA' => $_POST['CodMaquina'])));*/
							$callSetup = xajaxCall('iniciarSetup', array($dadosOrdemAtual));
							$callAjuste = xajaxCall('ajusteMaquina', array(array('MAQUINA' => $_POST['CodMaquina'], 'ORDEM' => $dadosOrdemAtual['ORDEM'])), "Iniciar ajuste para a máquina {$descMaquina}?");
							?>
                            <table width="100%" border="0" cellpadding="3" cellspacing="0">
                              <tr>
                                <td style="padding-left:5px;" width="65%">
									<? //=button(array('name' => 'Inicia' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Inicia Produção', 'disabled' => (($ordemData['STATUS'] != 'S') and ($row['qtd_conferida'] != $ordemData['QTDE_TOTAL'])), 'onclick' => "\"{$callIniciar}\""))?>
									<?=button(array('name' => 'Inicia' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Inicia Produção', 'onclick' => "\"{$callIniciar}\""))?>
									<?=button(array('name'=>'Finaliza' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Finaliza Produção', 'onclick' => "\"{$callFinaliza}\"", 'hidden' => true))?>
									<? //button(array('name' => 'IniciaSetup', 'type' => 'button', 'caption' => 'Iniciar Setup', 'onclick' => "\"{$callSetup}\""))?>
									<? //if ($dadosOrdemAtual['UMB'] != 'PC' || $maquinaDT) {?>
										<?=button(array('name'=>'AmarradoExtra' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Amarrado Extra', 'onclick' => "\"exibeAmarradoExtra(this);\"", 'hidden' => true)) ?>
									<? //}?>
									<?=button(array("name" => "RelatorioConfirmacaoComponentes", "caption" => "Relatório de Confirmação de Componentes", "type" => "button", "onclick" => "relatorioConfirmacaoComponentes();"))?>
                                    <div id="AmarradoPecas<?=$dadosOrdemAtual['ORDEM']?>" style="display:none">Número de Peças: <?=field(array('name'=>'AmarradoPecasQtde' . $dadosOrdemAtual['ORDEM'], 'type' => 'integer', 'caption' => 'Peças do Amarrado', 'size' => 15, 'maxlength' => 12, 'onkeypress' => 'insereAmarradoExtra(event, "' . $dadosOrdemAtual['ORDEM'] . '")'))?></div>
                                    <div id="LancamentoPecas<?=$dadosOrdemAtual['ORDEM']?>" style="display:none">Número de Peças por Amarrado: <?=field(array('name'=>'LancamentoPecasQtde' . $dadosOrdemAtual['ORDEM'], 'type' => 'integer', 'caption' => 'Peças por Amarrado', 'size' => 15, 'maxlength' => 12, 'onkeypress' => 'insereAmarradosPecas(event, "' . $dadosOrdemAtual['ORDEM'] . '")'))?></div>
                                </td>
                                <td width="35%" align="right" style="padding-right:5px;">
									<?//=button(array('name' => 'Ajuste' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Ajuste', 'onclick' => "\"{$callAjuste}\"", 'hidden' => true))?>
									<?=button(array('name' => 'Acompanhar' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Acompanhamento', 'onclick' => "\"exibeAcompanhamento('{$dadosOrdemAtual['ORDEM']}')\"", 'hidden' => true))?>
									<?/*button(array('name' => 'Abortar' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Abortar Ordem', 'onclick' => "\"{$callAbortar}\"", 'hidden' => true))*/?>
                                    <?=button(array('name' => 'Observacao' . $ordemData['ORDEM'], 'type' => 'button', 'caption' => 'Observação', 'onclick' => "\"exibeObs('{$ordemData['ORDEM']}')\"", 'hidden' => true));?>
									<?
										
										//$dadosOrdemAtual['fichaTecnica'] = busca_fichaTecnica($dadosOrdemAtual['CENTRO_TRABALHO'].'.pdf');
										
									$FoundFicha = False;
									if ($dadosOrdemAtual['CNPJCPF'] == '') {
									
										$refFicha = sqlFirstCell("SELECT referencia FROM excecoes_ficha_tecnica WHERE cliente = ? AND material = ?", array($dadosOrdemAtual['CLIENTE'], $dadosOrdemAtual['MATERIAL']));

										if ($refFicha != '') {
											if ( file_exists((trim($sharePointUrl)).trim($dadosOrdemAtual['MATERIAL']).'_'.trim($refFicha).'.pdf') ) {
												$FoundFicha = True;
												$dadosOrdemAtual['fichaTecnica'] = (trim($sharePointUrl)).trim($dadosOrdemAtual['MATERIAL']).'_'.trim($refFicha).'.pdf';
											}
										}
										
									} else {
									//Verifica se existe a Ficha Técnica
										if ( file_exists(trim($sharePointUrl).trim($dadosOrdemAtual['MATERIAL']).'_'.trim($dadosOrdemAtual['CNPJCPF']).'.pdf') ) {
											$FoundFicha = True;
											$dadosOrdemAtual['fichaTecnica'] = trim($sharePointUrl).trim($dadosOrdemAtual['MATERIAL']).'_'.trim($dadosOrdemAtual['CNPJCPF']).'.pdf';
										}
									}
									if (!$FoundFicha) {
										$dadosOrdemAtual['fichaTecnica'] = (trim($sharePointUrl)).$dadosOrdemAtual['CENTRO_TRABALHO'].'.pdf';
									}
									//echo  '<br> Botton - '.$dadosOrdemAtual['fichaTecnica'].'<br> Centro '.$dadosOrdemAtual['CENTRO_TRABALHO'].'<br> Found '. (string)$FoundFicha .'<br> CNPJ '.$dadosOrdemAtual['CNPJCPF'] .'<br> Material '.$dadosOrdemAtual['MATERIAL'] .'<br> Ref '.trim($refFicha) .'<br> Cliente '.$dadosOrdemAtual['CLIENTE'].'<br> ' ;
								
										button(array('name' => 'fichaTecnica' . $dadosOrdemAtual['ORDEM'], 'type' => 'button', 'caption' => 'Ficha Técnica', 'onclick' => "\"exibeFichaTecnica('{$dadosOrdemAtual['fichaTecnica']}')\""/*, 'hidden' => true*/));

									?>
                                </td>
                              </tr>
                              <tr id="Acompanhamento<?=$dadosOrdemAtual['ORDEM']?>" style="display:none"><td colspan="2"></td></tr>
                            </table>
							<?
						}
						?><hr color="black" /><?
					} else {
						$i++;
					}
				}
			} else {
				$aProduzir = false;
			}
			if (!$emProducao && !@$aProduzir) {
			?>
			<table width="100%" cellpadding="3" cellspacing="1">
			  <tr>
			    <td align="center">Este maquinário não possui ordens a produzir ou em produção.</td>
			  </tr>
			</table>
			<?
			} else {
			?>
            <table width="100%" border="0" cellpadding="3" cellspacing="0">
              <tr>
                <td align="right" style="padding-right:5px;font-size:14px;">
                  Quantidade Total: <b><?=$TotalQuantidade?></b>
                </td>
              </tr>
            </table>
			<?
			}
		}
		?>
	  </td>
  </tr>
</table>
</form>
</table>
<?
// chamar sempre "rodape" ao inves de "linha" no final,
// pois a funcao rodape fecha o <body> e o <html>
rodape();
?>
<script>
$(document).ready(function()
	{
		xajax_buscar_horarioMaquina("<?php echo $_POST['CodMaquina']; ?>");		
	});
</script>