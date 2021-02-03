<?php
$montant="1.16"; 
 /** 
 * Fonction de conversion Nombre => Lettres (Echelle longue jusque decilliard (10exp63))
 * Version en langue FRANCAISE (variante : France)
 * Les d�cimales sont arrondies � deux chiffres avant traitement.
 * 
 * Source cr��e par C. Kaiser le 11/02/2011
 * Version 1.2 (23/09/2015)
 * 
 * NB : L'�chelle peut �tre compl�t�e facilement (il suffit de rajouter les valeurs suivantes dans le tableau "$milliers")
 */

function conversion($n,$cpt){
	if(!is_numeric($n)){return false;}
	
	while(strlen($n)<3){$n='0'.$n;} // On rajoute les 0 pour obtenir une chaine de 3 chiffres
	if(preg_match('#^[0-9]{3}$#',$n)){$C=substr($n,0,1); $D=substr($n,1,1); $U=substr($n,2,1);}
	else{return false;}
	//Initialisation
	$valeur='';
	$chiffre=array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf');
	$nombre=array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix');
	//Conversion
	if($D==0){$valeur=$chiffre[$U];} 							// 1 � 10 (ne retourne pas "z�ro" pour 0)
	elseif($D==1){$valeur=$chiffre[$U+10];} 					// 11 � 19
	elseif($D>=2 && $D<=6){										// 20 � 69
		if($U==0){$valeur=$nombre[$D];}								// x0
		elseif($U==1){$valeur=$nombre[$D].' et un';}				// x1
		else{$valeur=$nombre[$D].'-'.$chiffre[$U];}					// x2 � x9
	}
	elseif($D==7){$valeur=$nombre[6].'-'.$chiffre[$U+10];}		// 70 � 79
	elseif($D==8){												// 80 � 89
		if($U==0){
			if($cpt!=1){$valeur=$nombre[$D].'s';}				// tous les 80 sauf ...
			else{$valeur=$nombre[$D];}							// 80 000 => quatre-vingt mille (cas particulier)
		}
		else{$valeur=$nombre[$D].'-'.$chiffre[$U];}				// 81 � 89
	}
	elseif($D==9){$valeur=$nombre[8].'-'.$chiffre[$U+10];}		// 90 � 99
	//centaines
	if($C==1){													//1xx
		if(strlen($valeur)==0){$valeur='cent';}						// 100
		else{$valeur='cent'.' '.$valeur;} 							// 101 � 199
	}	// 1xx				
	elseif($C!=0){												// 2xx � 9xx
		if(strlen($valeur)==0){										// [2-9]00
			if($cpt==1){$valeur=$chiffre[$C].' cent';}					// cas particulier (ex: 300 000 : trois cent mille)
			else{$valeur=$chiffre[$C].' cents';}						// [2-9]00 ... le reste (ex: 300 000 000 : trois cents millions)
		}
		else{$valeur=$chiffre[$C].' cent '.$valeur;}				// le reste
	}
	else{}														// 0xx
	//nettoyage
	unset($n,$C,$D,$U,$chiffre,$nombre,$esp);
	//renvoi
	return $valeur;
}

function nombre_en_lettre($montant)
{
	if(!preg_match('#^[,.0-9]+$#',$montant)){return false;}
	else{
		// Initialisation
		$retour='';
		// Param�trage
		$devise=array("S"=>'�uro', "P"=>'�uros');						// devise "enti�re"
		$ssdevise=array("S"=>'centime', "P"=>'centimes');				// devise "d�cimales"
		// Pr�paration
		if(preg_match('#,#',$montant)){$montant=preg_replace('#,#','.',$montant);}//on remplace la virgule potentielle par un point

		if(preg_match('#.#',$montant)){$temp=explode('.',$montant);}
		else{$temp=array(0=>$montant,1=>'00');}

		$valeur["e"]=$temp[0];											// valeur enti�re
		$valeur["d"]=round("0.".$temp[1],2)*100;						// valeur d�cimale (arrondie � 2 chiffres)
		unset($temp);													// nettoyage
		// Conversion
		if($valeur["e"]==0 || $valeur["e"]==''){
			if($montant==0){ return "z�ro ".$devise["S"];}				//pas de d�cimales => retour : "z�ro ..."
			else{$retour='';} 											// il y a des d�cimales => pas de z�ro
		}
		elseif($valeur["e"]==1){$retour="un ".$devise["S"];}
		else{
			//gestion des noms par milliers
			$milliers=array('','mille','million','milliard','billion','billiard','trillion','trilliard','quadrillion','quadrilliard','quintillion','quintilliard','sextillion','sextilliard','septillion','septilliard','octillion','octilliard','nonillion','nonilliard','decillion','decilliard');
			//r�cup�rer des chaines par milliers
			while(strlen($valeur["e"])%3!=0){$valeur["e"]='0'.$valeur["e"];} //d'abord compl�ter la chaine si besoin (multiple de 3 chiffres)
			$chaine=$valeur["e"];
			$cpt=0;
			while(strlen($chaine)>0){
				$souschaine=substr($chaine,strlen($chaine)-3,3);
				if($souschaine!="000")								// pas de traitement si nul
				{
					$temp=conversion($souschaine,$cpt);
					switch($temp){
						case '': $retour.=' - erreur sur le millier n�'.($cpt+1).' - ';	break;
						case 'un':	if($cpt==1){$retour=$milliers[$cpt].' '.$retour;} 	// 1xxx : pas de "un mille ..." mais "mille ..."
									else{$retour='un '.$milliers[$cpt].' '.$retour;}	// un ... le reste (million, milliard, etc...)
							break;
						default:	if($cpt==0){$retour=$temp.' '.$milliers[$cpt].' '.$retour;}		// pas de "millier dans ce cas"
									elseif($cpt==1){$retour=$temp.' '.$milliers[$cpt].' '.$retour;}	// "mille" pas "milles"
									else{$retour=$temp.' '.$milliers[$cpt].'s '.$retour;} 			// X (millions, milliards, etc...)
							break;
					}
				}
				//pr�paration pour la suite
				$chaine=substr($chaine,0,strlen($chaine)-3);			// on supprime les 3 derniers chiffres
				$cpt++;													// on passe aux milliers sup�rieurs
			}
			unset($temp,$cpt,$chaine, $souschaine);						// nettoyage
			$retour=trim($retour).' '.$devise["P"];						// d'office pluriel, car 0 et 1 trait�s � part
		}
		/* chiffres d�cimaux */
		if($valeur["d"]==0 || $valeur["d"]==''){}						// rien � rajouter
		elseif($valeur["d"]==01){$retour.=' un '.$ssdevise["S"];}		// ajout du centi�me
		else{
			$temp=conversion($valeur["d"],-1);
			if(strlen($temp)>0){$retour.=' '.$temp.' '.$ssdevise["P"];}	// ajout des autres possibilit�s
			unset($temp);
		}
		preg_replace('#  #',' ',$retour);								//au cas o�
		unset($devise,$ssdevise,$valeur,$milliers);						//un petit nettoyage avant d�part
		return trim($retour);											//on renvoi le tout
	}
}

// $retour = nombre_en_lettre($montant);
// echo $retour;
?>
 <form method="post">
 <fieldset style="padding: 2">
 <legend><font color="#0000FF" size="4">Transcrire en lettres les montants en chiffres</font></legend>
 <p align="left"><font color="#0000FF">
 Montant en chiffres : (sous la forme 111111111111111.11) <input type="text" name="saisie" size="25" maxlenght="12" tabindex="1" style="font-family: Tahoma; color: #0000FF; font-size: 14pt">
 <font color="#0000FF">&nbsp;<font size="5">�uros</font></font><p align="center">&nbsp;</p>
 <input type="submit" value"Transcrire">
 <input type="reset" value="Annuler"><br>
 </fieldset>
 </form>
<?php 

if(isset($_POST['saisie']))      
{
$montant=$_POST['saisie'];
if (($montant!=0) or ($montant!=""))
  {echo "	<br>Montant en chiffres : ".number_format($montant, 2, ',', ' ')." �uros<br>"; 
  echo "Montant en lettres :".nombre_en_lettre($montant);
  }else{
    echo "Veuillez saisir un montant en chiffres !";
  }
}
?>