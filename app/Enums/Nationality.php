<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Nationality: string implements HasLabel
{
    case Bahraini = 'Bahraini';
    case Kuwaiti = 'Kuwaiti';
    case Omani = 'Omani';
    case Qatari = 'Qatari';
    case Saudi = 'Saudi';
    case Emirati = 'Emirati';
    case Afghan = 'Afghan';
    case Albanian = 'Albanian';
    case Algerian = 'Algerian';
    case Argentine = 'Argentine';
    case Armenian = 'Armenian';
    case Australian = 'Australian';
    case Austrian = 'Austrian';
    case Azerbaijani = 'Azerbaijani';
    case Bangladeshi = 'Bangladeshi';
    case Belgian = 'Belgian';
    case Bosnian = 'Bosnian';
    case Brazilian = 'Brazilian';
    case Bulgarian = 'Bulgarian';
    case Cameroonian = 'Cameroonian';
    case Canadian = 'Canadian';
    case Chadian = 'Chadian';
    case Chilean = 'Chilean';
    case Chinese = 'Chinese';
    case Colombian = 'Colombian';
    case Croatian = 'Croatian';
    case Cypriot = 'Cypriot';
    case Czech = 'Czech';
    case Danish = 'Danish';
    case Egyptian = 'Egyptian';
    case English = 'English';
    case Eritrean = 'Eritrean';
    case Ethiopian = 'Ethiopian';
    case Finnish = 'Finnish';
    case French = 'French';
    case Georgian = 'Georgian';
    case German = 'German';
    case Ghanaian = 'Ghanaian';
    case Greek = 'Greek';
    case Hungarian = 'Hungarian';
    case Indian = 'Indian';
    case Indonesian = 'Indonesian';
    case Iranian = 'Iranian';
    case Iraqi = 'Iraqi';
    case Irish = 'Irish';
    case Italian = 'Italian';
    case Ivorian = 'Ivorian';
    case Japanese = 'Japanese';
    case Jordanian = 'Jordanian';
    case Kazakh = 'Kazakh';
    case Kenyan = 'Kenyan';
    case Lebanese = 'Lebanese';
    case Libyan = 'Libyan';
    case Malaysian = 'Malaysian';
    case Malian = 'Malian';
    case Mauritanian = 'Mauritanian';
    case Mexican = 'Mexican';
    case Moroccan = 'Moroccan';
    case Nepalese = 'Nepalese';
    case Dutch = 'Dutch';
    case NewZealander = 'New Zealander';
    case Nigerian = 'Nigerian';
    case Macedonian = 'Macedonian';
    case Norwegian = 'Norwegian';
    case Pakistani = 'Pakistani';
    case Palestinian = 'Palestinian';
    case Filipino = 'Filipino';
    case Polish = 'Polish';
    case Portuguese = 'Portuguese';
    case Romanian = 'Romanian';
    case Russian = 'Russian';
    case Scottish = 'Scottish';
    case Senegalese = 'Senegalese';
    case Serbian = 'Serbian';
    case Singaporean = 'Singaporean';
    case Somali = 'Somali';
    case SouthAfrican = 'South African';
    case SouthKorean = 'South Korean';
    case SouthSudanese = 'South Sudanese';
    case Spanish = 'Spanish';
    case SriLankan = 'Sri Lankan';
    case Sudanese = 'Sudanese';
    case Swedish = 'Swedish';
    case Swiss = 'Swiss';
    case Syrian = 'Syrian';
    case Tanzanian = 'Tanzanian';
    case Thai = 'Thai';
    case Tunisian = 'Tunisian';
    case Turkish = 'Turkish';
    case Ugandan = 'Ugandan';
    case Ukrainian = 'Ukrainian';
    case British = 'British';
    case American = 'American';
    case Uruguayan = 'Uruguayan';
    case Uzbek = 'Uzbek';
    case Welsh = 'Welsh';
    case Yemeni = 'Yemeni';

    public function getLabel(): ?string
    {
        return __('enums.nationalities.'.$this->name);
    }

    public function flag(): string
    {
        return match ($this) {
            self::Bahraini => Country::Bahrain->flag(),
            self::Kuwaiti => Country::Kuwait->flag(),
            self::Omani => Country::Oman->flag(),
            self::Qatari => Country::Qatar->flag(),
            self::Saudi => Country::SaudiArabia->flag(),
            self::Emirati => Country::UnitedArabEmirates->flag(),
            self::Afghan => Country::Afghanistan->flag(),
            self::Albanian => Country::Albania->flag(),
            self::Algerian => Country::Algeria->flag(),
            self::Argentine => Country::Argentina->flag(),
            self::Armenian => Country::Armenia->flag(),
            self::Australian => Country::Australia->flag(),
            self::Austrian => Country::Austria->flag(),
            self::Azerbaijani => Country::Azerbaijan->flag(),
            self::Bangladeshi => Country::Bangladesh->flag(),
            self::Belgian => Country::Belgium->flag(),
            self::Bosnian => Country::BosniaAndHerzegovina->flag(),
            self::Brazilian => Country::Brazil->flag(),
            self::Bulgarian => Country::Bulgaria->flag(),
            self::Cameroonian => Country::Cameroon->flag(),
            self::Canadian => Country::Canada->flag(),
            self::Chadian => Country::Chad->flag(),
            self::Chilean => Country::Chile->flag(),
            self::Chinese => Country::China->flag(),
            self::Colombian => Country::Colombia->flag(),
            self::Croatian => Country::Croatia->flag(),
            self::Cypriot => Country::Cyprus->flag(),
            self::Czech => Country::CzechRepublic->flag(),
            self::Danish => Country::Denmark->flag(),
            self::Egyptian => Country::Egypt->flag(),
            self::English => Country::England->flag(),
            self::Eritrean => Country::Eritrea->flag(),
            self::Ethiopian => Country::Ethiopia->flag(),
            self::Finnish => Country::Finland->flag(),
            self::French => Country::France->flag(),
            self::Georgian => Country::Georgia->flag(),
            self::German => Country::Germany->flag(),
            self::Ghanaian => Country::Ghana->flag(),
            self::Greek => Country::Greece->flag(),
            self::Hungarian => Country::Hungary->flag(),
            self::Indian => Country::India->flag(),
            self::Indonesian => Country::Indonesia->flag(),
            self::Iranian => Country::Iran->flag(),
            self::Iraqi => Country::Iraq->flag(),
            self::Irish => Country::Ireland->flag(),
            self::Italian => Country::Italy->flag(),
            self::Ivorian => Country::IvoryCoast->flag(),
            self::Japanese => Country::Japan->flag(),
            self::Jordanian => Country::Jordan->flag(),
            self::Kazakh => Country::Kazakhstan->flag(),
            self::Kenyan => Country::Kenya->flag(),
            self::Lebanese => Country::Lebanon->flag(),
            self::Libyan => Country::Libya->flag(),
            self::Malaysian => Country::Malaysia->flag(),
            self::Malian => Country::Mali->flag(),
            self::Mauritanian => Country::Mauritania->flag(),
            self::Mexican => Country::Mexico->flag(),
            self::Moroccan => Country::Morocco->flag(),
            self::Nepalese => Country::Nepal->flag(),
            self::Dutch => Country::Netherlands->flag(),
            self::NewZealander => Country::NewZealand->flag(),
            self::Nigerian => Country::Nigeria->flag(),
            self::Macedonian => Country::NorthMacedonia->flag(),
            self::Norwegian => Country::Norway->flag(),
            self::Pakistani => Country::Pakistan->flag(),
            self::Palestinian => Country::Palestine->flag(),
            self::Filipino => Country::Philippines->flag(),
            self::Polish => Country::Poland->flag(),
            self::Portuguese => Country::Portugal->flag(),
            self::Romanian => Country::Romania->flag(),
            self::Russian => Country::Russia->flag(),
            self::Scottish => Country::Scotland->flag(),
            self::Senegalese => Country::Senegal->flag(),
            self::Serbian => Country::Serbia->flag(),
            self::Singaporean => Country::Singapore->flag(),
            self::Somali => Country::Somalia->flag(),
            self::SouthAfrican => Country::SouthAfrica->flag(),
            self::SouthKorean => Country::SouthKorea->flag(),
            self::SouthSudanese => Country::SouthSudan->flag(),
            self::Spanish => Country::Spain->flag(),
            self::SriLankan => Country::SriLanka->flag(),
            self::Sudanese => Country::Sudan->flag(),
            self::Swedish => Country::Sweden->flag(),
            self::Swiss => Country::Switzerland->flag(),
            self::Syrian => Country::Syria->flag(),
            self::Tanzanian => Country::Tanzania->flag(),
            self::Thai => Country::Thailand->flag(),
            self::Tunisian => Country::Tunisia->flag(),
            self::Turkish => Country::Turkey->flag(),
            self::Ugandan => Country::Uganda->flag(),
            self::Ukrainian => Country::Ukraine->flag(),
            self::British => Country::UnitedKingdom->flag(),
            self::American => Country::UnitedStates->flag(),
            self::Uruguayan => Country::Uruguay->flag(),
            self::Uzbek => Country::Uzbekistan->flag(),
            self::Welsh => Country::Wales->flag(),
            self::Yemeni => Country::Yemen->flag(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = trim($case->flag().' '.$case->getLabel());
        }

        return $options;
    }
}
