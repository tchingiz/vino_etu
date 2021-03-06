<?php

/**
 * Class MonSQL
 * Classe qui génère ma connection à MySQL à travers un singleton
 *
 *
 * @author Jonathan Martel
 * @version 1.0
 *
 *
 *
 */
class SAQ extends Modele
{

	const DUPLICATION = 'duplication';
	const ERREURDB = 'erreurdb';
	const INSERE = 'Nouvelle bouteille insérée';

	private static $_webpage;
	private static $_status;
	private $stmt;

	public function __construct()
	{
		parent::__construct();
		if (!($this->stmt = $this->_db->prepare("INSERT INTO vino__bouteille(nom, id_type, image, code_saq, pays, description, prix_saq, url_saq, url_img, format) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
			echo "Echec de la préparation : (" . $mysqli->errno . ") " . $mysqli->error;
		}
	}

	/**
	 * getProduits
	 * @param int $nombre
	 * @param int $debut
	 */
	public function getProduits($nombre = 24, $debut = 1)
	{
		$s = curl_init();
		//$url = "https://www.saq.com/fr/produits/vin/vin-rouge?p=1&product_list_limit=24&product_list_order=name_asc";
		//curl_setopt($s, CURLOPT_URL, "http://www.saq.com/webapp/wcs/stores/servlet/SearchDisplay?searchType=&orderBy=&categoryIdentifier=06&showOnly=product&langId=-2&beginIndex=" . $debut . "&tri=&metaData=YWRpX2YxOjA8TVRAU1A%2BYWRpX2Y5OjE%3D&pageSize=" . $nombre . "&catalogId=50000&searchTerm=*&sensTri=&pageView=&facet=&categoryId=39919&storeId=20002");
		//curl_setopt($s, CURLOPT_URL, "https://www.saq.com/webapp/wcs/stores/servlet/SearchDisplay?categoryIdentifier=06&showOnly=product&langId=-2&beginIndex=" . $debut . "&pageSize=" . $nombre . "&catalogId=50000&searchTerm=*&categoryId=39919&storeId=20002");
		//curl_setopt($s, CURLOPT_URL, $url);
		curl_setopt($s, CURLOPT_URL, "https://www.saq.com/fr/produits/vin?p=" . $debut . "&product_list_limit=" . $nombre . "");
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($s, CURLOPT_FOLLOWLOCATION, 1);

		self::$_webpage = curl_exec($s);
		self::$_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
		curl_close($s);

		$doc = new DOMDocument();
		$doc->recover = true;
		$doc->strictErrorChecking = false;
		@$doc->loadHTML(self::$_webpage);
		$elements = $doc->getElementsByTagName("li");
		$i = 0;
?>
		<table>
			<thead>
				<tr>
					<th>Nom de la Bouteille</th>
					<th>Format</th>
					<th>Type</th>
					<th>Pays</th>
					<th>Prix</th>
					<th>Nouveauté</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($elements as $key => $noeud) {
					if (strpos($noeud->getAttribute('class'), "product-item") !== false) {
						//echo $this->get_inner_html($noeud);
						$info = self::recupereInfo($noeud);
						//echo "<p>" . $info->nom;
						$retour = $this->ajouteProduit($info);
				?>
						<tr>
							<td data-column="Nom de la bouteille"><?php echo $info->nom; ?></td>
							<td data-column="Format"><?php echo $info->desc->format; ?></td>
							<td data-column="Type"><?php echo $info->desc->type; ?></td>
							<td data-column="Pays"><?php echo $info->desc->pays; ?></td>
							<td data-column="Prix"><?php echo $info->prix; ?></td>
							<td data-column="Nouveaute"><?php echo $retour->raison; ?></td>
						</tr>
				<?php

						//echo "<br>Code de retour : " . $retour->raison . "<br>";
						if ($retour->succes == false) {
							//echo "<pre>";
							//var_dump($info);
							//echo "</pre>";
							//echo "<br>";
						} else {
							$i++;
						}
						//echo "</p>";
					}
				}
				?>
			</tbody>
		</table>
<?php
		return $i;
	}

	private function get_inner_html($node)
	{
		$innerHTML = '';
		$children = $node->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $child->ownerDocument->saveXML($child);
		}
		return $innerHTML;
	}

	private function nettoyerEspace($chaine)
	{
		return preg_replace('/\s+/', ' ', $chaine);
	}

	private function recupereInfo($noeud)
	{
		$info = new stdClass();
		$items = $noeud->getElementsByTagName("img");
		foreach ($items as $item) {
			if ($item->getAttribute('class') == 'product-image-photo') {
				$info->img = $item->getAttribute('src');
			}
		}

		$info->img = substr($info->img, 6); // Enlever le https au début du lien
		//var_dump($info->img);

		$a_titre = $noeud->getElementsByTagName("a")->item(0);
		$info->url = $a_titre->getAttribute('href');

		$info->nom = self::nettoyerEspace(trim($a_titre->textContent));

		// Type, format et pays
		$aElements = $noeud->getElementsByTagName("strong");
		foreach ($aElements as $node) {
			if ($node->getAttribute('class') == 'product product-item-identity-format') {
				$info->desc = new stdClass();
				$info->desc->texte = $node->textContent;
				$info->desc->texte = self::nettoyerEspace($info->desc->texte);
				$aDesc = explode("|", $info->desc->texte); // Type, Format, Pays
				if (count($aDesc) == 3) {

					$info->desc->type = trim($aDesc[0]);
					$info->desc->format = trim($aDesc[1]);
					$info->desc->pays = trim($aDesc[2]);
				}
				$info->desc->texte = trim($info->desc->texte);
			}
		}

		//Code SAQ
		$aElements = $noeud->getElementsByTagName("div");
		foreach ($aElements as $node) {
			if ($node->getAttribute('class') == 'saq-code') {
				if (preg_match("/\d+/", $node->textContent, $aRes)) {
					$info->desc->code_SAQ = trim($aRes[0]);
				}
			}
		}

		$aElements = $noeud->getElementsByTagName("span");
		foreach ($aElements as $node) {
			if ($node->getAttribute('class') == 'price') {
				//var_dump($node);
				$info->prix = trim($node->textContent);
			}
		}
		//var_dump($info);
		return $info;
	}

	private function ajouteProduit($bte)
	{
		//var_dump($bte);
		$retour = new stdClass();
		$retour->succes = false;
		$retour->raison = '';

		//$price = substr(($bte -> prix), 0,5);
		$price = substr(($bte->prix), 0, 5);
		//str_replace(',', '.', $price);
		$t = str_replace(',', '.', $price);
		$a = (float) $t;

		$rows = $this->_db->query("select id from vino__bouteille_type where type = '" . $bte->desc->type . "'");

		if ($rows->num_rows == 1) {
			$type = $rows->fetch_assoc();
			//var_dump($type);
			$type = $type['id'];

			$rows = $this->_db->query("select id from vino__bouteille where code_saq = '" . $bte->desc->code_SAQ . "'");
			if ($rows->num_rows < 1) {
				$this->stmt->bind_param("sissssdsss", $bte->nom, $type, $bte->img, $bte->desc->code_SAQ, $bte->desc->pays, $bte->desc->texte, $a, $bte->url, $bte->img, $bte->desc->format);
				$retour->succes = $this->stmt->execute();
				$retour->raison = self::INSERE;

				//var_dump($this->stmt);
			} else {
				$retour->succes = false;
				$retour->raison = self::DUPLICATION;
			}
		} else {
			$retour->succes = false;
			$retour->raison = self::ERREURDB;
		}
		return $retour;
	}
}
