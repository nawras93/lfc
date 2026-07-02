<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Country: string implements HasLabel
{
    case Bahrain = 'Bahrain';
    case Kuwait = 'Kuwait';
    case Oman = 'Oman';
    case Qatar = 'Qatar';
    case SaudiArabia = 'Saudi Arabia';
    case UnitedArabEmirates = 'United Arab Emirates';
    case Afghanistan = 'Afghanistan';
    case Albania = 'Albania';
    case Algeria = 'Algeria';
    case Argentina = 'Argentina';
    case Armenia = 'Armenia';
    case Australia = 'Australia';
    case Austria = 'Austria';
    case Azerbaijan = 'Azerbaijan';
    case Bangladesh = 'Bangladesh';
    case Belgium = 'Belgium';
    case BosniaAndHerzegovina = 'Bosnia and Herzegovina';
    case Brazil = 'Brazil';
    case Bulgaria = 'Bulgaria';
    case Cameroon = 'Cameroon';
    case Canada = 'Canada';
    case Chad = 'Chad';
    case Chile = 'Chile';
    case China = 'China';
    case Colombia = 'Colombia';
    case Croatia = 'Croatia';
    case Cyprus = 'Cyprus';
    case CzechRepublic = 'Czech Republic';
    case Denmark = 'Denmark';
    case Egypt = 'Egypt';
    case England = 'England';
    case Eritrea = 'Eritrea';
    case Ethiopia = 'Ethiopia';
    case Finland = 'Finland';
    case France = 'France';
    case Georgia = 'Georgia';
    case Germany = 'Germany';
    case Ghana = 'Ghana';
    case Greece = 'Greece';
    case Hungary = 'Hungary';
    case India = 'India';
    case Indonesia = 'Indonesia';
    case Iran = 'Iran';
    case Iraq = 'Iraq';
    case Ireland = 'Ireland';
    case Italy = 'Italy';
    case IvoryCoast = 'Ivory Coast';
    case Japan = 'Japan';
    case Jordan = 'Jordan';
    case Kazakhstan = 'Kazakhstan';
    case Kenya = 'Kenya';
    case Lebanon = 'Lebanon';
    case Libya = 'Libya';
    case Malaysia = 'Malaysia';
    case Mali = 'Mali';
    case Mauritania = 'Mauritania';
    case Mexico = 'Mexico';
    case Morocco = 'Morocco';
    case Nepal = 'Nepal';
    case Netherlands = 'Netherlands';
    case NewZealand = 'New Zealand';
    case Nigeria = 'Nigeria';
    case NorthMacedonia = 'North Macedonia';
    case Norway = 'Norway';
    case Pakistan = 'Pakistan';
    case Palestine = 'Palestine';
    case Philippines = 'Philippines';
    case Poland = 'Poland';
    case Portugal = 'Portugal';
    case Romania = 'Romania';
    case Russia = 'Russia';
    case Scotland = 'Scotland';
    case Senegal = 'Senegal';
    case Serbia = 'Serbia';
    case Singapore = 'Singapore';
    case Somalia = 'Somalia';
    case SouthAfrica = 'South Africa';
    case SouthKorea = 'South Korea';
    case SouthSudan = 'South Sudan';
    case Spain = 'Spain';
    case SriLanka = 'Sri Lanka';
    case Sudan = 'Sudan';
    case Sweden = 'Sweden';
    case Switzerland = 'Switzerland';
    case Syria = 'Syria';
    case Tanzania = 'Tanzania';
    case Thailand = 'Thailand';
    case Tunisia = 'Tunisia';
    case Turkey = 'Turkey';
    case Uganda = 'Uganda';
    case Ukraine = 'Ukraine';
    case UnitedKingdom = 'United Kingdom';
    case UnitedStates = 'United States';
    case Uruguay = 'Uruguay';
    case Uzbekistan = 'Uzbekistan';
    case Wales = 'Wales';
    case Yemen = 'Yemen';

    public function getLabel(): ?string
    {
        return __('enums.countries.'.$this->name);
    }

    public function flag(): string
    {
        return match ($this) {
            self::Bahrain => '🇧🇭',
            self::Kuwait => '🇰🇼',
            self::Oman => '🇴🇲',
            self::Qatar => '🇶🇦',
            self::SaudiArabia => '🇸🇦',
            self::UnitedArabEmirates => '🇦🇪',
            self::Afghanistan => '🇦🇫',
            self::Albania => '🇦🇱',
            self::Algeria => '🇩🇿',
            self::Argentina => '🇦🇷',
            self::Armenia => '🇦🇲',
            self::Australia => '🇦🇺',
            self::Austria => '🇦🇹',
            self::Azerbaijan => '🇦🇿',
            self::Bangladesh => '🇧🇩',
            self::Belgium => '🇧🇪',
            self::BosniaAndHerzegovina => '🇧🇦',
            self::Brazil => '🇧🇷',
            self::Bulgaria => '🇧🇬',
            self::Cameroon => '🇨🇲',
            self::Canada => '🇨🇦',
            self::Chad => '🇹🇩',
            self::Chile => '🇨🇱',
            self::China => '🇨🇳',
            self::Colombia => '🇨🇴',
            self::Croatia => '🇭🇷',
            self::Cyprus => '🇨🇾',
            self::CzechRepublic => '🇨🇿',
            self::Denmark => '🇩🇰',
            self::Egypt => '🇪🇬',
            self::England => '🏴',
            self::Eritrea => '🇪🇷',
            self::Ethiopia => '🇪🇹',
            self::Finland => '🇫🇮',
            self::France => '🇫🇷',
            self::Georgia => '🇬🇪',
            self::Germany => '🇩🇪',
            self::Ghana => '🇬🇭',
            self::Greece => '🇬🇷',
            self::Hungary => '🇭🇺',
            self::India => '🇮🇳',
            self::Indonesia => '🇮🇩',
            self::Iran => '🇮🇷',
            self::Iraq => '🇮🇶',
            self::Ireland => '🇮🇪',
            self::Italy => '🇮🇹',
            self::IvoryCoast => '🇨🇮',
            self::Japan => '🇯🇵',
            self::Jordan => '🇯🇴',
            self::Kazakhstan => '🇰🇿',
            self::Kenya => '🇰🇪',
            self::Lebanon => '🇱🇧',
            self::Libya => '🇱🇾',
            self::Malaysia => '🇲🇾',
            self::Mali => '🇲🇱',
            self::Mauritania => '🇲🇷',
            self::Mexico => '🇲🇽',
            self::Morocco => '🇲🇦',
            self::Nepal => '🇳🇵',
            self::Netherlands => '🇳🇱',
            self::NewZealand => '🇳🇿',
            self::Nigeria => '🇳🇬',
            self::NorthMacedonia => '🇲🇰',
            self::Norway => '🇳🇴',
            self::Pakistan => '🇵🇰',
            self::Palestine => '🇵🇸',
            self::Philippines => '🇵🇭',
            self::Poland => '🇵🇱',
            self::Portugal => '🇵🇹',
            self::Romania => '🇷🇴',
            self::Russia => '🇷🇺',
            self::Scotland => '🏴',
            self::Senegal => '🇸🇳',
            self::Serbia => '🇷🇸',
            self::Singapore => '🇸🇬',
            self::Somalia => '🇸🇴',
            self::SouthAfrica => '🇿🇦',
            self::SouthKorea => '🇰🇷',
            self::SouthSudan => '🇸🇸',
            self::Spain => '🇪🇸',
            self::SriLanka => '🇱🇰',
            self::Sudan => '🇸🇩',
            self::Sweden => '🇸🇪',
            self::Switzerland => '🇨🇭',
            self::Syria => '🇸🇾',
            self::Tanzania => '🇹🇿',
            self::Thailand => '🇹🇭',
            self::Tunisia => '🇹🇳',
            self::Turkey => '🇹🇷',
            self::Uganda => '🇺🇬',
            self::Ukraine => '🇺🇦',
            self::UnitedKingdom => '🇬🇧',
            self::UnitedStates => '🇺🇸',
            self::Uruguay => '🇺🇾',
            self::Uzbekistan => '🇺🇿',
            self::Wales => '🏴',
            self::Yemen => '🇾🇪',
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
