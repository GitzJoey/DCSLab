<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brand::class;

    protected $brands = [
        'Apple', 'Samsung Group', 'Google', 'Microsoft', 'Verizon', 'General Electric', 'AT&T', 'Amazon', 'Walmart', 'IBM', 'Toyota', 'Coca Cola', 'China Mobile', 'T (Deutsche Telekom)', 'Wells Fargo', 'Vodafone', 'BMW', 'Shell', 'Volkswagen', 'HSBC', 'Bank of America', 'Mitsubishi Group', 'McDonalds', 'Citi', 'Home Depot', 'Mercedes-Benz', 'Walt Disney', 'Chase', 'Intel', 'ICBC', 'Honda', 'Nissan', 'American Express', 'Ford', 'Nike', 'Cisco', 'Oracle', 'Allianz', 'Siemens', 'Nestle', 'BNP Paribas', 'Santander', 'Orange', 'Mitsui Group', 'HP', 'Pepsi', 'UPS', 'Chevron', 'Axa', 'China Construction Bank', 'Hyundai Group', 'IKEA', 'CVS Caremark', 'Hitachi Group', 'Target', 'SoftBank', 'Agricultural Bank Of China', 'Tesco', 'MUFG', 'Bank of China', 'ExxonMobil', 'PWC', 'PetroChina', 'GDF Suez', 'China Unicom', 'NTT', 'Walgreens', 'Comcast', 'BT', 'Tata', 'Airbus', 'Total', 'Barclays', 'JP Morgan', 'China Telecom', 'Deloitte', 'Toshiba', 'ING (Group)', 'Sams Club', 'Deutsche Bank', 'Marlboro', 'FedEx', 'eBay', 'SAP', 'Fox', 'Generali Group', 'ALDI', 'Movistar', 'BP', 'Lowes', 'Sinopec', 'Sony', '3M', 'China Life', 'LG Group', 'H&M', 'KPMG', 'DHL', 'Panasonic', 'RBC', 'Philips', 'Accenture', 'EY', 'Sberbank', 'Boeing', 'TD Bank', 'Woolworths Group', 'LOreal', 'UBS', 'Bradesco', 'Renault', 'au', 'Costco', 'EDF', 'Goldman Sachs', 'Credit Suisse', 'Carrefour', 'Time Warner Cable', 'Starbucks', 'ItaÃº', 'UnitedHealth GROUP', 'Facebook', 'TimeWarner', 'E. ON', 'Ping An', 'Petronas', 'Bosch', 'China State Construction', 'Gazprom', 'Capital One', 'Visa', 'Honeywell', 'Sumitomo GROUP', 'Huawei', 'Subway', 'BBVA', 'NBC', 'Gillette', 'SK GROUP', 'Telstra', 'Dell', 'Macy', 'TCS', 'DirecTV', 'Metlife', 'Canon', 'Sky', 'Morgan Stanley', 'TIM', 'Sainsbury', 'Kelloggs', 'ASDA', 'SMFG', 'Societe Generale', 'Adidas', 'Eni', 'Caterpillar', 'Johnson & Johnson', 'Scotiabank', 'Mizuho', 'Prudential (UK)', 'Danone', 'QQ', 'O2', 'Enel', 'Ericsson', 'Vinci', 'Nordea', 'Cartier', 'Zara', 'Centurylink', 'Swiss Re', 'Paypal', 'Standard Chartered', 'Peugeot', 'Bank OF Montreal', 'Audi', 'Bell', 'Banco do Brasil', 'Bank OF Communications', 'Zurich', 'Hermes', 'Rabobank', 'Sprint', 'BHP Billiton', 'UniCredit', 'Telenor', 'AIG', 'Xbox', 'Medtronic', 'Chevrolet', 'Morrison', 'Avon', 'BASF', 'Coles', 'Mastercard', 'Cadbury', 'UNI0N Pacific', 'EMC', 'Gucci', 'Red Bull', 'Pantene', 'Warner Bros.', 'Jardines', 'Nivea', 'Kroger', 'Glencore Xstrata', 'Alibaba', 'CBS', 'Aetna', 'Statoil', 'Thomson Reuters', 'ANZ', 'Dove', 'Heinz', 'Nescafe', 'ESPN', 'Mclane Company', 'Louis Vuitton', 'Conocophillips', 'Groupe Casino', 'Munich Re', 'Iberdrola', 'Aeon', 'Purina', 'Prudential (US)', 'Pampers', 'Bridgestone', 'Petrobras', 'Nordstrom', 'Nomura', 'U.S.Bank', 'Emirates', 'Commonwealth Bank', 'Royal Mail', 'E.Leclerc', 'Shinhan Financial GROUP', 'Unilever', 'China Merchants Bank', 'MTN', 'Kia Motors', 'EE', 'Aegon', 'Suzuki', 'Polo Ralph Lauren', 'Yahoo !', 'WellPoint', 'ABB', 'Publix', 'Esso', 'CNOOC', 'Bayer', 'Fiat', 'Randstad', 'Mobil', 'Bud Light', 'Swisscom', 'Johnson Controls', 'Baidu', 'Daimler', 'Heineken', 'BBC', 'Rolex', 'CIBC', 'Marks & Spencer', 'Prada', 'National Australia Bank', 'STC', 'Johnnie Walker', 'DZ Bank', 'CNP Assurances', 'KT', 'Westpac', 'Fujitsu', 'Allstate', 'Sharp', 'Uniqlo', 'General Motors', 'Garnier', 'Dish Network', 'Arcelormittal', 'Caixa', 'Rogers', 'Delta', 'Virgin Media', 'MTV', 'Saint - Gobain', 'Michelin', 'British Gas', '7 - Eleven', 'Aviva', 'BNY Mellon', 'Estee Lauder', 'Playstation', 'Rio Tinto', 'Dai - Ichi Life', 'Metro', 'SK Telecom', 'SFR', 'Chow Tai Fook', 'RWE', 'Mazda', 'Chanel', 'Rolls - Royce', 'John Deere', 'Exxon', 'La Poste', 'Lukoil', 'Wrigleys', 'PNC', 'China Minsheng Bank', 'Safeway', 'Emerson Electric', 'Land Rover', 'Qualcomm', 'Alcatel - Lucent', 'GMC', 'Magnit', 'Antarchile', 'Telus', 'Citroen', '3', 'Sprite', 'Budweiser', 'QVC', 'Burberry', 'MINI', 'Subaru', 'Coach', 'Auchan', 'Asahi', 'Lexus', 'Moutai', 'Lufthansa', 'Telcel', 'CPIC', 'Pfizer', 'Omega', 'Berkshire Hathaway', 'Schneider Electric', 'Continental', 'Kyocera', 'Lotte GROUP', 'LIC', 'Southern Company', 'Xerox', 'Enbridge', 'Bombardier', 'LancÃ´me', 'Olay', 'State Bank OF India', 'Kohls', 'Marubeni', 'Ferrari', 'Discovery', 'DBS', 'Express Script', 'Huggies', 'Whole Foods', 'Mountain Dew', 'Sherwin - Williams', 'Best Buy', 'TEPCO', 'TUI Travel', 'Winston', 'Holcim', 'Ergo', 'Harley - Davidson', 'Royal Bank OF Scotland', 'VictoriaSecret', 'Lenovo', 'McKinsey', 'NatWest', 'Maersk', 'Lockheed Martin', 'PICC', 'National Grid', 'Lays Potato Chips', 'SYSCO', 'Travelers', 'United', 'MAN', 'Airtel', 'Western Digital', 'Bank OF America Merrill Lynch', 'Chunghwa', 'MCC', 'Activision Blizzard', 'Fluor', 'Nippon Steel', 'Duracell', 'Nec', 'Dollar General', 'AIA', 'Gas NATURAL', 'Capgemini', 'PTT', 'Lloyds', 'Claro', 'The Co - operative GROUP', 'AutoZone', 'Goodyear', 'Tiffany & Co.', 'KPN', 'Geico', 'Michael Kors', 'Endesa', 'MTS', 'Dongfeng', 'Commerzbank', 'J.C.Penney', 'Johnsons', 'KFC', 'Optus', 'Skol', 'Mapfre', 'Reliance', 'Volvo', 'Adobe', 'Media Markt & Saturn', 'KEPCO', 'Porsche', 'ZTE', 'Deutsche Post', 'KOGAS', 'Raytheon', 'Colgate', 'Beeline', 'Fujifilm GROUP', 'ADP', 'Ageas', 'Staples', 'BAE Systems', 'KBC', 'KB Financial GROUP', 'UPC', 'Roche', 'Etisalat', 'Otis', 'Isuzu', 'Natixis', 'Ecopetrol', 'Carmax', 'LAFARGE', 'Schlumberger', 'Mckesson', 'Novartis', 'Erste Bank', 'Rosneft', 'Arla', 'Christian Dior', 'SSE', 'Daiwa House Industry', 'Gatorade', 'ONGC', 'General Dynamics', '21st Century Fox', 'Next', 'Industrial Bank', 'DNB', 'Halifax', 'BG', 'Wolseley', 'VTB', 'Singapore Airlines', 'Mercadona', 'Progressive', 'Veolia', 'United Technologies', 'Discover', 'Bed Bath & Beyond', 'ABN AMRO', 'Skanska', 'Toys R Us', 'NETFLIX', 'Megafon', 'Ace', 'Canadian National Railway', 'Svenska Handelsbanken', 'Indian Oil', 'State Street', 'EDP', 'Blackrock', 'Credit Agricole', 'Halliburton', 'CSX', 'Falabella', 'Safran', 'Procter & Gamble', 'Unicharm Corp', 'Fanta', 'Aflac', 'WeChat', 'GS GROUP', 'Eiffage', 'Sodexo', 'Kraft', 'Glaxosmithkline', 'China CITIC Bank', 'McCain',
    ];

    public function definition()
    {
        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($this->brands),
        ];
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'name' => $this->craftName($str),
            ];
        });
    }

    private function craftName(string $str)
    {
        $text = $this->faker->randomElement($this->brands);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
