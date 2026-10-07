<?php
/**
 * @file CountriesEnum.php
 * @brief Énumération des pays disponibles dans l'application.
 *
 * Chaque valeur correspond à un code ISO 3166-1 alpha-2 et est associée
 * à un libellé lisible pour l'affichage dans le formulaire d'inscription.
 *
 * @author Brandon
 * @author Aymen
 * @author Imen
 * @author Milan
 * @date 2026
 */
namespace App\Content\Enums;

/**
 * @brief Énumération des pays disponibles dans l'application.
 *
 * Chaque valeur correspond à un code ISO 3166-1 alpha-2 et est associée
 * à un libellé lisible pour l'affichage dans le formulaire d'inscription.
 */
enum CountriesEnum : string
{
    case Afghanistan = 'AF';case AfriqueDuSud = 'ZA';
    case Aland = 'AX';
    case Albanie = 'AL';
    case Algerie = 'DZ';
    case Allemagne = 'DE';
    case Andorre = 'AD';
    case Angola = 'AO';
    case Anguilla = 'AI';
    case Antarctique = 'AQ';
    case AntigueEtBarbuda = 'AG';
    case ArabieSaoudite = 'SA';
    case Argentine = 'AR';
    case Armenie = 'AM';
    case Aruba = 'AW';
    case Australie = 'AU';
    case Autriche = 'AT';
    case Azerbaidjan = 'AZ';
    case Bahamas = 'BS';
    case Bahrein = 'BH';
    case Bangladesh = 'BD';
    case Barbade = 'BB';
    case Belgique = 'BE';
    case Belize = 'BZ';
    case Benin = 'BJ';
    case Bermudes = 'BM';
    case Bhoutan = 'BT';
    case Bielorussie = 'BY';
    case Birmanie = 'MM';
    case Bolivie = 'BO';
    case BonaireSaintEustacheEtSaba = 'BQ';
    case BosnieHerzegovine = 'BA';
    case Botswana = 'BW';
    case Bresil = 'BR';
    case Brunei = 'BN';
    case Bulgarie = 'BG';
    case BurkinaFaso = 'BF';
    case Burundi = 'BI';
    case Cambodge = 'KH';
    case Cameroun = 'CM';
    case Canada = 'CA';
    case CapVert = 'CV';
    case Chili = 'CL';
    case Chine = 'CN';
    case Chypre = 'CY';
    case Colombie = 'CO';
    case Comores = 'KM';
    case CongoBrazzaville = 'CG';
    case CongoKinshasa = 'CD';
    case CoreeDuNord = 'KP';
    case CoreeDuSud = 'KR';
    case CostaRica = 'CR';
    case CoteDIvoire = 'CI';
    case Croatie = 'HR';
    case Cuba = 'CU';
    case Curacao = 'CW';
    case Danemark = 'DK';
    case Djibouti = 'DJ';
    case Dominique = 'DM';
    case Egypte = 'EG';
    case EmiratsArabesUnis = 'AE';
    case Equateur = 'EC';
    case Erythree = 'ER';
    case Espagne = 'ES';
    case Estonie = 'EE';
    case Eswatini = 'SZ';
    case EtatsUnis = 'US';
    case Ethiopie = 'ET';
    case Fidji = 'FJ';
    case Finlande = 'FI';
    case France = 'FR';
    case Gabon = 'GA';
    case Gambie = 'GM';
    case Georgie = 'GE';
    case GeorgieDuSudEtIlesSandwichDuSud = 'GS';
    case Ghana = 'GH';
    case Gibraltar = 'GI';
    case Grece = 'GR';
    case Grenade = 'GD';
    case Groenland = 'GL';
    case Guadeloupe = 'GP';
    case Guam = 'GU';
    case Guatemala = 'GT';
    case Guernesey = 'GG';
    case Guinee = 'GN';
    case GuineeBissau = 'GW';
    case GuineeEquatoriale = 'GQ';
    case Guyana = 'GY';
    case Guyane = 'GF';
    case Haiti = 'HT';
    case Honduras = 'HN';
    case HongKong = 'HK';
    case Hongrie = 'HU';
    case IleBouvet = 'BV';
    case IleChristmas = 'CX';
    case IleDeMan = 'IM';
    case IleNorfolk = 'NF';
    case IlesCaimans = 'KY';
    case IlesCocos = 'CC';
    case IlesCook = 'CK';
    case IlesFeroe = 'FO';
    case IlesHeardEtMcDonald = 'HM';
    case IlesMalouines = 'FK';
    case IlesMariannesDuNord = 'MP';
    case IlesMarshall = 'MH';
    case IlesMineuresEloigneesDesEtatsUnis = 'UM';
    case IlesPitcairn = 'PN';
    case IlesSalomon = 'SB';
    case IlesTurquesEtCaiques = 'TC';
    case IlesViergesBritanniques = 'VG';
    case IlesViergesDesEtatsUnis = 'VI';
    case Inde = 'IN';
    case Indonesie = 'ID';
    case Irak = 'IQ';
    case Iran = 'IR';
    case Irlande = 'IE';
    case Islande = 'IS';
    case Israel = 'IL';
    case Italie = 'IT';
    case Jamaique = 'JM';
    case Japon = 'JP';
    case Jersey = 'JE';
    case Jordanie = 'JO';
    case Kazakhstan = 'KZ';
    case Kenya = 'KE';
    case Kirghizistan = 'KG';
    case Kiribati = 'KI';
    case Koweit = 'KW';
    case LaReunion = 'RE';
    case Laos = 'LA';
    case Lesotho = 'LS';
    case Lettonie = 'LV';
    case Liban = 'LB';
    case Liberia = 'LR';
    case Libye = 'LY';
    case Liechtenstein = 'LI';
    case Lituanie = 'LT';
    case Luxembourg = 'LU';
    case Macao = 'MO';
    case MacedoineDuNord = 'MK';
    case Madagascar = 'MG';
    case Malaisie = 'MY';
    case Malawi = 'MW';
    case Maldives = 'MV';
    case Mali = 'ML';
    case Malte = 'MT';
    case Maroc = 'MA';
    case Martinique = 'MQ';
    case Maurice = 'MU';
    case Mauritanie = 'MR';
    case Mayotte = 'YT';
    case Mexique = 'MX';
    case Micronesie = 'FM';
    case Moldavie = 'MD';
    case Monaco = 'MC';
    case Mongolie = 'MN';
    case Montenegro = 'ME';
    case Montserrat = 'MS';
    case Mozambique = 'MZ';
    case Namibie = 'NA';
    case Nauru = 'NR';
    case Nepal = 'NP';
    case Nicaragua = 'NI';
    case Niger = 'NE';
    case Nigeria = 'NG';
    case Niue = 'NU';
    case Norvege = 'NO';
    case NouvelleCaledonie = 'NC';
    case NouvelleZelande = 'NZ';
    case Oman = 'OM';
    case Ouganda = 'UG';
    case Ouzbekistan = 'UZ';
    case Pakistan = 'PK';
    case Palaos = 'PW';
    case Palestine = 'PS';
    case Panama = 'PA';
    case PapouasieNouvelleGuinee = 'PG';
    case Paraguay = 'PY';
    case PaysBas = 'NL';
    case Perou = 'PE';
    case Philippines = 'PH';
    case Pologne = 'PL';
    case PolynesieFrancaise = 'PF';
    case PortoRico = 'PR';
    case Portugal = 'PT';
    case Qatar = 'QA';
    case RepubliqueCentrafricaine = 'CF';
    case RepubliqueDominicaine = 'DO';
    case Roumanie = 'RO';
    case RoyaumeUni = 'GB';
    case Russie = 'RU';
    case Rwanda = 'RW';
    case SaharaOccidental = 'EH';
    case SaintBarthelemy = 'BL';
    case SaintChristopheEtNieves = 'KN';
    case SaintMarin = 'SM';
    case SaintMartin = 'MF';
    case SaintMartinPartieNeerlandaise = 'SX';
    case SaintPierreEtMiquelon = 'PM';
    case SaintSiege = 'VA';
    case SaintVincentEtLesGrenadines = 'VC';
    case SainteHelene = 'SH';
    case SainteLucie = 'LC';
    case Salvador = 'SV';
    case Samoa = 'WS';
    case SamoaAmericaines = 'AS';
    case SaoTomeEtPrincipe = 'ST';
    case Senegal = 'SN';
    case Serbie = 'RS';
    case Seychelles = 'SC';
    case SierraLeone = 'SL';
    case Singapour = 'SG';
    case Slovaquie = 'SK';
    case Slovenie = 'SI';
    case Somalie = 'SO';
    case Soudan = 'SD';
    case SoudanDuSud = 'SS';
    case SriLanka = 'LK';
    case Suede = 'SE';
    case Suisse = 'CH';
    case Suriname = 'SR';
    case SvalbardEtJanMayen = 'SJ';
    case Syrie = 'SY';
    case Tadjikistan = 'TJ';
    case Taiwan = 'TW';
    case Tanzanie = 'TZ';
    case Tchad = 'TD';
    case Tchequie = 'CZ';
    case TerritoireBritanniqueDeLOceanIndien = 'IO';
    case TerresAustralesFrancaises = 'TF';
    case Thailande = 'TH';
    case TimorOriental = 'TL';
    case Togo = 'TG';
    case Tokelau = 'TK';
    case Tonga = 'TO';
    case TriniteEtTobago = 'TT';
    case Tunisie = 'TN';
    case Turkmenistan = 'TM';
    case Turquie = 'TR';
    case Tuvalu = 'TV';
    case Ukraine = 'UA';
    case Uruguay = 'UY';
    case Vanuatu = 'VU';
    case Venezuela = 'VE';
    case Vietnam = 'VN';
    case WallisEtFutuna = 'WF';
    case Yemen = 'YE';
    case Zambie = 'ZM';
    case Zimbabwe = 'ZW';
    /**
     * @brief Tableau de correspondance entre le code ISO du pays et son libellé.
     *
     * @var array<string, string>
     */
    private const LABELS = ['ZA' => 'Afrique du Sud',
    'AX' => 'Îles Åland',
    'DZ' => 'Algérie',
    'AG' => 'Antigua-et-Barbuda',
    'SA' => 'Arabie saoudite',
    'AM' => 'Arménie',
    'AZ' => 'Azerbaïdjan',
    'BH' => 'Bahreïn',
    'BJ' => 'Bénin',
    'BY' => 'Biélorussie',
    'BQ' => 'Bonaire, Saint-Eustache et Saba',
    'BA' => 'Bosnie-Herzégovine',
    'BR' => 'Brésil',
    'BF' => 'Burkina Faso',
    'CV' => 'Cap-Vert',
    'CG' => 'Congo (Brazzaville)',
    'CD' => 'Congo (Kinshasa)',
    'KP' => 'Corée du Nord',
    'KR' => 'Corée du Sud',
    'CR' => 'Costa Rica',
    'CI' => "Côte d'Ivoire",
    'CW' => 'Curaçao',
    'EG' => 'Égypte',
    'AE' => 'Émirats arabes unis',
    'EC' => 'Équateur',
    'ER' => 'Érythrée',
    'US' => 'États-Unis',
    'ET' => 'Éthiopie',
    'GE' => 'Géorgie',
    'GS' => 'Géorgie du Sud et îles Sandwich du Sud',
    'GR' => 'Grèce',
    'GN' => 'Guinée',
    'GW' => 'Guinée-Bissau',
    'GQ' => 'Guinée équatoriale',
    'HT' => 'Haïti',
    'HK' => 'Hong Kong',
    'BV' => 'Île Bouvet',
    'CX' => 'Île Christmas',
    'IM' => 'Île de Man',
    'NF' => 'Île Norfolk',
    'KY' => 'Îles Caïmans',
    'CC' => 'Îles Cocos',
    'CK' => 'Îles Cook',
    'FO' => 'Îles Féroé',
    'HM' => 'Îles Heard-et-MacDonald',
    'FK' => 'Îles Malouines',
    'MP' => 'Îles Mariannes du Nord',
    'MH' => 'Îles Marshall',
    'UM' => 'Îles mineures éloignées des États-Unis',
    'PN' => 'Îles Pitcairn',
    'SB' => 'Îles Salomon',
    'TC' => 'Îles Turques-et-Caïques',
    'VG' => 'Îles Vierges britanniques',
    'VI' => 'Îles Vierges des États-Unis',
    'ID' => 'Indonésie',
    'IL' => 'Israël',
    'JM' => 'Jamaïque',
    'KW' => 'Koweït',
    'RE' => 'La Réunion',
    'LR' => 'Libéria',
    'MK' => 'Macédoine du Nord',
    'FM' => 'Micronésie',
    'ME' => 'Monténégro',
    'NP' => 'Népal',
    'NG' => 'Nigéria',
    'NO' => 'Norvège',
    'NC' => 'Nouvelle-Calédonie',
    'NZ' => 'Nouvelle-Zélande',
    'UZ' => 'Ouzbékistan',
    'PG' => 'Papouasie-Nouvelle-Guinée',
    'NL' => 'Pays-Bas',
    'PE' => 'Pérou',
    'PF' => 'Polynésie française',
    'PR' => 'Porto Rico',
    'CF' => 'République centrafricaine',
    'DO' => 'République dominicaine',
    'GB' => 'Royaume-Uni',
    'EH' => 'Sahara occidental',
    'BL' => 'Saint-Barthélemy',
    'KN' => 'Saint-Christophe-et-Niévès',
    'SM' => 'Saint-Marin',
    'MF' => 'Saint-Martin (partie française)',
    'SX' => 'Saint-Martin (partie néerlandaise)',
    'PM' => 'Saint-Pierre-et-Miquelon',
    'VA' => 'Saint-Siège (Vatican)',
    'VC' => 'Saint-Vincent-et-les-Grenadines',
    'SH' => 'Sainte-Hélène, Ascension et Tristan da Cunha',
    'LC' => 'Sainte-Lucie',
    'SV' => 'Salvador',
    'AS' => 'Samoa américaines',
    'ST' => 'Sao Tomé-et-Principe',
    'SN' => 'Sénégal',
    'SL' => 'Sierra Leone',
    'SI' => 'Slovénie',
    'SS' => 'Soudan du Sud',
    'LK' => 'Sri Lanka',
    'SE' => 'Suède',
    'SJ' => 'Svalbard et Jan Mayen',
    'TW' => 'Taïwan',
    'CZ' => 'Tchéquie',
    'IO' => "Territoire britannique de l'océan Indien",
    'TF' => 'Terres australes françaises',
    'TH' => 'Thaïlande',
    'TL' => 'Timor oriental',
    'TT' => 'Trinité-et-Tobago',
    'TM' => 'Turkménistan',
    'WF' => 'Wallis-et-Futuna',
    'YE' => 'Yémen'];

    /**
     * @brief Retourne le libellé lisible associé au pays.
     *
     * @return string Nom complet du pays ou son identifiant si aucun libellé
     * n'est trouvé dans la table de correspondance.
     */
    public function label(): string
    {
        return self::LABELS[$this->value] ?? $this->name;
    }
}