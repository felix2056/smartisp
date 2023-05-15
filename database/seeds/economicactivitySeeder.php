<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class economicactivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0',
                'Description'=>'No especificado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0111',
                'Description'=>'Cultivo de cereales (excepto arroz), legumbres y semillas oleaginosas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0112',
                'Description'=>'Cultivo de arroz'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0113',
                'Description'=>'Cultivo de hortalizas, raíces y tubérculos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0114',
                'Description'=>'Cultivo de tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0115',
                'Description'=>'Cultivo de plantas textiles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0119',
                'Description'=>'Otros cultivos transitorios n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0121',
                'Description'=>'Cultivo de frutas tropicales y subtropicales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0122',
                'Description'=>'Cultivo de plátano y banano'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0123',
                'Description'=>'Cultivo de café'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0124',
                'Description'=>'Cultivo de caña de azúcar'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0125',
                'Description'=>'Cultivo de flor de corte'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0126',
                'Description'=>'Cultivo de palma para aceite (palma africana) y otros frutos oleaginosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0127',
                'Description'=>'Cultivo de plantas con las que se preparan bebidas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0128',
                'Description'=>'Cultivo de especias y de plantas aromáticas y medicinales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0129',
                'Description'=>'Otros cultivos permanentes n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0130',
                'Description'=>'Propagación de plantas (actividades de los viveros, excepto viveros forestales)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0141',
                'Description'=>'Cría de ganado bovino y bufalino'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0142',
                'Description'=>'Cría de caballos y otros equinos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0143',
                'Description'=>'Cría de ovejas y cabras'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0144',
                'Description'=>'Cría de ganado porcino'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0145',
                'Description'=>'Cría de aves de corral'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0149',
                'Description'=>'Cría de otros animales n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0150',
                'Description'=>'Explotación mixta (agrícola y pecuaria)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0161',
                'Description'=>'Actividades de apoyo a la agricultura'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0162',
                'Description'=>'Actividades de apoyo a la ganadería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0163',
                'Description'=>'Actividades posteriores a la cosecha'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0164',
                'Description'=>'Tratamiento de semillas para propagación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0170',
                'Description'=>'Caza ordinaria y mediante trampas y actividades de servicios conexas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0210',
                'Description'=>'Silvicultura y otras actividades forestales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0220',
                'Description'=>'Extracción de madera'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0230',
                'Description'=>'Recolección de productos forestales diferentes a la madera'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0240',
                'Description'=>'Servicios de apoyo a la silvicultura'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0311',
                'Description'=>'Pesca marítima'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0312',
                'Description'=>'Pesca de agua dulce'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0321',
                'Description'=>'Acuicultura marítima'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0322',
                'Description'=>'Acuicultura de agua dulce'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0510',
                'Description'=>'Extracción de hulla (carbón de piedra)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0520',
                'Description'=>'Extracción de carbón lignito'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0610',
                'Description'=>'Extracción de petróleo crudo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0620',
                'Description'=>'Extracción de gas natural'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0710',
                'Description'=>'Extracción de minerales de hierro'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0721',
                'Description'=>'Extracción de minerales de uranio y de torio'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0722',
                'Description'=>'Extracción de oro y otros metales preciosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0723',
                'Description'=>'Extracción de minerales de níquel'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0729',
                'Description'=>'Extracción de otros minerales metalíferos no ferrosos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0811',
                'Description'=>'Extracción de piedra, arena, arcillas comunes, yeso y anhidrita'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0812',
                'Description'=>'Extracción de arcillas de uso industrial, caliza, caolín y bentonitas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0820',
                'Description'=>'Extracción de esmeraldas, piedras preciosas y semipreciosas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0891',
                'Description'=>'Extracción de minerales para la fabricación de abonos y productos químicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0892',
                'Description'=>'Extracción de halita (sal)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0899',
                'Description'=>'Extracción de otros minerales no metálicos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0910',
                'Description'=>'Actividades de apoyo para la extracción de petróleo y de gas natural'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0990',
                'Description'=>'Actividades de apoyo para otras actividades de explotación de minas y canteras'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1011',
                'Description'=>'Procesamiento y conservación de carne y productos cárnicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1012',
                'Description'=>'Procesamiento y conservación de pescados, crustáceos y moluscos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1020',
                'Description'=>'Procesamiento y conservación de frutas, legumbres, hortalizas y tubérculos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1030',
                'Description'=>'Elaboración de aceites y grasas de origen vegetal y animal'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1040',
                'Description'=>'Elaboración de productos lácteos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1051',
                'Description'=>'Elaboración de productos de molinería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1052',
                'Description'=>'Elaboración de almidones y productos derivados del almidón'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1061',
                'Description'=>'Trilla de café'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1062',
                'Description'=>'Descafeinado, tostión y molienda del café'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1063',
                'Description'=>'Otros derivados del café'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1071',
                'Description'=>'Elaboración y refinación de azúcar'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1072',
                'Description'=>'Elaboración de panela'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1081',
                'Description'=>'Elaboración de productos de panadería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1082',
                'Description'=>'Elaboración de cacao, chocolate y productos de confitería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1083',
                'Description'=>'Elaboración de macarrones, fideos, alcuzcuz y productos farináceos similares'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1084',
                'Description'=>'Elaboración de comidas y platos preparados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1089',
                'Description'=>'Elaboración de otros productos alimenticios n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1090',
                'Description'=>'Elaboración de alimentos preparados para animales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1101',
                'Description'=>'Destilación, rectificación y mezcla de bebidas alcohólicas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1102',
                'Description'=>'Elaboración de bebidas fermentadas no destiladas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1103',
                'Description'=>'Producción de malta, elaboración de cervezas y otras bebidas malteadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1104',
                'Description'=>'Elaboración de bebidas no alcohólicas, producción de aguas minerales y de otras aguas embotelladas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1200',
                'Description'=>'Elaboración de productos de tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1311',
                'Description'=>'Preparación e hilatura de fibras textiles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1312',
                'Description'=>'Tejeduría de productos textiles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1313',
                'Description'=>'Acabado de productos textiles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1391',
                'Description'=>'Fabricación de tejidos de punto y ganchillo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1392',
                'Description'=>'Confección de artículos con materiales textiles, excepto prendas de vestir'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1393',
                'Description'=>'Fabricación de tapetes y alfombras para pisos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1394',
                'Description'=>'Fabricación de cuerdas, cordeles, cables, bramantes y redes'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1399',
                'Description'=>'Fabricación de otros artículos textiles n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1410',
                'Description'=>'Confección de prendas de vestir, excepto prendas de piel'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1420',
                'Description'=>'Fabricación de artículos de piel'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1430',
                'Description'=>'Fabricación de artículos de punto y ganchillo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1511',
                'Description'=>'Curtido y recurtido de cueros; recurtido y teñido de pieles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1512',
                'Description'=>'Fabricación de artículos de viaje, bolsos de mano y artículos similares elaborados en cuero y fabricación de artículos de talabartería y guarnicionería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1513',
                'Description'=>'Fabricación de artículos de viaje, bolsos de mano y artículos similares; artículos de talabartería y guarnicionería elaborados en otros materiales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1521',
                'Description'=>'Fabricación de calzado de cuero y piel, con cualquier tipo de suela'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1522',
                'Description'=>'Fabricación de otros tipos de calzado, excepto calzado de cuero y piel'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1523',
                'Description'=>'Fabricación de partes del calzado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1610',
                'Description'=>'Aserrado, acepillado e impregnación de la madera'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1620',
                'Description'=>'Fabricación de hojas de madera para enchapado; fabricación de tableros contrachapados, tableros laminados, tableros de partículas y otros tableros y paneles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1630',
                'Description'=>'Fabricación de partes y piezas de madera, de carpintería y ebanistería para la construcción'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1640',
                'Description'=>'Fabricación de recipientes de madera'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1690',
                'Description'=>'Fabricación de otros productos de madera; fabricación de artículos de corcho, cestería y espartería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1701',
                'Description'=>'Fabricación de pulpas (pastas) celulósicas; papel y cartón'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1702',
                'Description'=>'Fabricación de papel y cartón ondulado (corrugado); fabricación de envases, empaques y de embalajes de papel y cartón.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1709',
                'Description'=>'Fabricación de otros artículos de papel y cartón'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1811',
                'Description'=>'Actividades de impresión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1812',
                'Description'=>'Actividades de servicios relacionados con la impresión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1820',
                'Description'=>'Producción de copias a partir de grabaciones originales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1910',
                'Description'=>'Fabricación de productos de hornos de coque'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1921',
                'Description'=>'Fabricación de productos de la refinación del petróleo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'1922',
                'Description'=>'Actividad de mezcla de combustibles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2011',
                'Description'=>'Fabricación de sustancias y productos químicos básicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2012',
                'Description'=>'Fabricación de abonos y compuestos inorgánicos nitrogenados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2013',
                'Description'=>'Fabricación de plásticos en formas primarias'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2014',
                'Description'=>'Fabricación de caucho sintético en formas primarias'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2021',
                'Description'=>'Fabricación de plaguicidas y otros productos químicos de uso agropecuario'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2022',
                'Description'=>'Fabricación de pinturas, barnices y revestimientos similares, tintas para impresión y masillas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2023',
                'Description'=>'Fabricación de jabones y detergentes, preparados para limpiar y pulir; perfumes y preparados de tocador'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2029',
                'Description'=>'Fabricación de otros productos químicos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2030',
                'Description'=>'Fabricación de fibras sintéticas y artificiales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2100',
                'Description'=>'Fabricación de productos farmacéuticos, sustancias químicas medicinales y productos botánicos de uso farmacéutico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2211',
                'Description'=>'Fabricación de llantas y neumáticos de caucho'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2212',
                'Description'=>'Reencauche de llantas usadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2219',
                'Description'=>'Fabricación de formas básicas de caucho y otros productos de caucho n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2221',
                'Description'=>'Fabricación de formas básicas de plástico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2229',
                'Description'=>'Fabricación de artículos de plástico n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2310',
                'Description'=>'Fabricación de vidrio y productos de vidrio'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2391',
                'Description'=>'Fabricación de productos refractarios'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2392',
                'Description'=>'Fabricación de materiales de arcilla para la construcción'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2393',
                'Description'=>'Fabricación de otros productos de cerámica y porcelana'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2394',
                'Description'=>'Fabricación de cemento, cal y yeso'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2395',
                'Description'=>'Fabricación de artículos de hormigón, cemento y yeso'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2396',
                'Description'=>'Corte, tallado y acabado de la piedra'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2399',
                'Description'=>'Fabricación de otros productos minerales no metálicos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2410',
                'Description'=>'Industrias básicas de hierro y de acero'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2421',
                'Description'=>'Industrias básicas de metales preciosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2429',
                'Description'=>'Industrias básicas de otros metales no ferrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2431',
                'Description'=>'Fundición de hierro y de acero'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2432',
                'Description'=>'Fundición de metales no ferrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2511',
                'Description'=>'Fabricación de productos metálicos para uso estructural'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2512',
                'Description'=>'Fabricación de tanques, depósitos y recipientes de metal, excepto los utilizados para el envase o transporte de mercancías'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2513',
                'Description'=>'Fabricación de generadores de vapor, excepto calderas de agua caliente para calefacción central'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2520',
                'Description'=>'Fabricación de armas y municiones'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2591',
                'Description'=>'Forja, prensado, estampado y laminado de metal; pulvimetalurgia'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2592',
                'Description'=>'Tratamiento y revestimiento de metales; mecanizado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2593',
                'Description'=>'Fabricación de artículos de cuchillería, herramientas de mano y artículos de ferretería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2599',
                'Description'=>'Fabricación de otros productos elaborados de metal n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2610',
                'Description'=>'Fabricación de componentes y tableros electrónicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2620',
                'Description'=>'Fabricación de computadoras y de equipo periférico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2630',
                'Description'=>'Fabricación de equipos de comunicación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2640',
                'Description'=>'Fabricación de aparatos electrónicos de consumo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2651',
                'Description'=>'Fabricación de equipo de medición, prueba, navegación y control'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2652',
                'Description'=>'Fabricación de relojes'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2660',
                'Description'=>'Fabricación de equipo de irradiación y equipo electrónico de uso médico y terapéutico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2670',
                'Description'=>'Fabricación de instrumentos ópticos y equipo fotográfico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2680',
                'Description'=>'Fabricación de medios magnéticos y ópticos para almacenamiento de datos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2711',
                'Description'=>'Fabricación de motores, generadores y transformadores eléctricos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2712',
                'Description'=>'Fabricación de aparatos de distribución y control de la energía eléctrica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2720',
                'Description'=>'Fabricación de pilas, baterías y acumuladores eléctricos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2731',
                'Description'=>'Fabricación de hilos y cables eléctricos y de fibra óptica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2732',
                'Description'=>'Fabricación de dispositivos de cableado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2740',
                'Description'=>'Fabricación de equipos eléctricos de iluminación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2750',
                'Description'=>'Fabricación de aparatos de uso doméstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2790',
                'Description'=>'Fabricación de otros tipos de equipo eléctrico n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2811',
                'Description'=>'Fabricación de motores, turbinas, y partes para motores de combustión interna'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2812',
                'Description'=>'Fabricación de equipos de potencia hidráulica y neumática'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2813',
                'Description'=>'Fabricación de otras bombas, compresores, grifos y válvulas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2814',
                'Description'=>'Fabricación de cojinetes, engranajes, trenes de engranajes y piezas de transmisión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2815',
                'Description'=>'Fabricación de hornos, hogares y quemadores industriales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2816',
                'Description'=>'Fabricación de equipo de elevación y manipulación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2817',
                'Description'=>'Fabricación de maquinaria y equipo de oficina (excepto computadoras y equipo periférico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2818',
                'Description'=>'Fabricación de herramientas manuales con motor'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2819',
                'Description'=>'Fabricación de otros tipos de maquinaria y equipo de uso general n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2821',
                'Description'=>'Fabricación de maquinaria agropecuaria y forestal'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2822',
                'Description'=>'Fabricación de máquinas formadoras de metal y de máquinas herramienta'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2823',
                'Description'=>'Fabricación de maquinaria para la metalurgia'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2824',
                'Description'=>'Fabricación de maquinaria para explotación de minas y canteras y para obras de construcción'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2825',
                'Description'=>'Fabricación de maquinaria para la elaboración de alimentos, bebidas y tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2826',
                'Description'=>'Fabricación de maquinaria para la elaboración de productos textiles, prendas de vestir y cueros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2829',
                'Description'=>'Fabricación de otros tipos de maquinaria y equipo de uso especial n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2910',
                'Description'=>'Fabricación de vehículos automotores y sus motores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2920',
                'Description'=>'Fabricación de carrocerías para vehículos automotores; fabricación de remolques y semirremolques'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'2930',
                'Description'=>'Fabricación de partes, piezas (autopartes) y accesorios (lujos) para vehículos automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3011',
                'Description'=>'Construcción de barcos y de estructuras flotantes'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3012',
                'Description'=>'Construcción de embarcaciones de recreo y deporte'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3020',
                'Description'=>'Fabricación de locomotoras y de material rodante para ferrocarriles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3030',
                'Description'=>'Fabricación de aeronaves, naves espaciales y de maquinaria conexa'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3040',
                'Description'=>'Fabricación de vehículos militares de combate'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3091',
                'Description'=>'Fabricación de motocicletas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3092',
                'Description'=>'Fabricación de bicicletas y de sillas de ruedas para personas con discapacidad'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3099',
                'Description'=>'Fabricación de otros tipos de equipo de transporte n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3110',
                'Description'=>'Fabricación de muebles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3120',
                'Description'=>'Fabricación de colchones y somieres'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3210',
                'Description'=>'Fabricación de joyas, bisutería y artículos conexos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3220',
                'Description'=>'Fabricación de instrumentos musicales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3230',
                'Description'=>'Fabricación de artículos y equipo para la práctica del deporte'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3240',
                'Description'=>'Fabricación de juegos, juguetes y rompecabezas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3250',
                'Description'=>'Fabricación de instrumentos, aparatos y materiales médicos y odontológicos (incluido mobiliario'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3290',
                'Description'=>'Otras industrias manufactureras n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3311',
                'Description'=>'Mantenimiento y reparación especializado de productos elaborados en metal'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3312',
                'Description'=>'Mantenimiento y reparación especializado de maquinaria y equipo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3313',
                'Description'=>'Mantenimiento y reparación especializado de equipo electrónico y óptico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3314',
                'Description'=>'Mantenimiento y reparación especializado de equipo eléctrico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3315',
                'Description'=>'Mantenimiento y reparación especializado de equipo de transporte, excepto los vehículos automotores, motocicletas y bicicletas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3319',
                'Description'=>'Mantenimiento y reparación de otros tipos de equipos y sus componentes n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3320',
                'Description'=>'Instalación especializada de maquinaria y equipo industrial'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3511',
                'Description'=>'Generación de energía eléctrica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3512',
                'Description'=>'Transmisión de energía eléctrica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3513',
                'Description'=>'Distribución de energía eléctrica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3514',
                'Description'=>'Comercialización de energía eléctrica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3520',
                'Description'=>'Producción de gas; distribución de combustibles gaseosos por tuberías'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3530',
                'Description'=>'Suministro de vapor y aire acondicionado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3600',
                'Description'=>'Captación, tratamiento y distribución de agua'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3700',
                'Description'=>'Evacuación y tratamiento de aguas residuales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3811',
                'Description'=>'Recolección de desechos no peligrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3812',
                'Description'=>'Recolección de desechos peligrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3821',
                'Description'=>'Tratamiento y disposición de desechos no peligrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3822',
                'Description'=>'Tratamiento y disposición de desechos peligrosos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3830',
                'Description'=>'Recuperación de materiales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'3900',
                'Description'=>'Actividades de saneamiento ambiental y otros servicios de gestión de desechos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4111',
                'Description'=>'Construcción de edificios residenciales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4112',
                'Description'=>'Construcción de edificios no residenciales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4210',
                'Description'=>'Construcción de carreteras y vías de ferrocarril'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4220',
                'Description'=>'Construcción de proyectos de servicio público'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4290',
                'Description'=>'Construcción de otras obras de ingeniería civil'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4311',
                'Description'=>'Demolición'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4312',
                'Description'=>'Preparación del terreno'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4321',
                'Description'=>'Instalaciones eléctricas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4322',
                'Description'=>'Instalaciones de fontanería, calefacción y aire acondicionado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4329',
                'Description'=>'Otras instalaciones especializadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4330',
                'Description'=>'Terminación y acabado de edificios y obras de ingeniería civil'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4390',
                'Description'=>'Otras actividades especializadas para la construcción de edificios y obras de ingeniería civil'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4511',
                'Description'=>'Comercio de vehículos automotores nuevos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4512',
                'Description'=>'Comercio de vehículos automotores usados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4520',
                'Description'=>'Mantenimiento y reparación de vehículos automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4530',
                'Description'=>'Comercio de partes, piezas (autopartes) y accesorios (lujos) para vehículos automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4541',
                'Description'=>'Comercio de motocicletas y de sus partes, piezas y accesorios'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4542',
                'Description'=>'Mantenimiento y reparación de motocicletas y de sus partes y piezas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4610',
                'Description'=>'Comercio al por mayor a cambio de una retribución o por contrata'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4620',
                'Description'=>'Comercio al por mayor de materias primas agropecuarias; animales vivos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4631',
                'Description'=>'Comercio al por mayor de productos alimenticios'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4632',
                'Description'=>'Comercio al por mayor de bebidas y tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4641',
                'Description'=>'Comercio al por mayor de productos textiles, productos confeccionados para uso doméstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4642',
                'Description'=>'Comercio al por mayor de prendas de vestir'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4643',
                'Description'=>'Comercio al por mayor de calzado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4644',
                'Description'=>'Comercio al por mayor de aparatos y equipo de uso doméstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4645',
                'Description'=>'Comercio al por mayor de productos farmacéuticos, medicinales, cosméticos y de tocador'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4649',
                'Description'=>'Comercio al por mayor de otros utensilios domésticos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4651',
                'Description'=>'Comercio al por mayor de computadores, equipo periférico y programas de informática'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4652',
                'Description'=>'Comercio al por mayor de equipo, partes y piezas electrónicos y de telecomunicaciones'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4653',
                'Description'=>'Comercio al por mayor de maquinaria y equipo agropecuarios'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4659',
                'Description'=>'Comercio al por mayor de otros tipos de maquinaria y equipo n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4661',
                'Description'=>'Comercio al por mayor de combustibles sólidos, líquidos, gaseosos y productos conexos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4662',
                'Description'=>'Comercio al por mayor de metales y productos metalíferos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4663',
                'Description'=>'Comercio al por mayor de materiales de construcción, artículos de ferretería, pinturas, productos de vidrio, equipo y materiales de fontanería y calefacción'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4664',
                'Description'=>'Comercio al por mayor de productos químicos básicos, cauchos y plásticos en formas primarias y productos químicos de uso agropecuario'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4665',
                'Description'=>'Comercio al por mayor de desperdicios, desechos y chatarra'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4669',
                'Description'=>'Comercio al por mayor de otros productos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4690',
                'Description'=>'Comercio al por mayor no especializado'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4711',
                'Description'=>'Comercio al por menor en establecimientos no especializados con surtido compuesto principalmente por alimentos, bebidas o tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4719',
                'Description'=>'Comercio al por menor en establecimientos no especializados, con surtido compuesto principalmente por productos diferentes de alimentos (víveres en general), bebidas y tabaco'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4721',
                'Description'=>'Comercio al por menor de productos agrícolas para el consumo en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4722',
                'Description'=>'Comercio al por menor de leche, productos lácteos y huevos, en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4723',
                'Description'=>'Comercio al por menor de carnes (incluye aves de corral), productos cárnicos, pescados y productos de mar, en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4724',
                'Description'=>'Comercio al por menor de bebidas y productos del tabaco, en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4729',
                'Description'=>'Comercio al por menor de otros productos alimenticios n.c.p., en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4731',
                'Description'=>'Comercio al por menor de combustible para automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4732',
                'Description'=>'Comercio al por menor de lubricantes (aceites, grasas), aditivos y productos de limpieza para vehículos automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4741',
                'Description'=>'Comercio al por menor de computadores, equipos periféricos, programas de informática y equipos de telecomunicaciones en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4742',
                'Description'=>'Comercio al por menor de equipos y aparatos de sonido y de video, en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4751',
                'Description'=>'Comercio al por menor de productos textiles en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4752',
                'Description'=>'Comercio al por menor de artículos de ferretería, pinturas y productos de vidrio en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4753',
                'Description'=>'Comercio al por menor de tapices, alfombras y cubrimientos para paredes y pisos en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4754',
                'Description'=>'Comercio al por menor de electrodomésticos y gasodomésticos de uso doméstico, muebles y equipos de iluminación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4755',
                'Description'=>'Comercio al por menor de artículos y utensilios de uso doméstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4759',
                'Description'=>'Comercio al por menor de otros artículos domésticos en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4761',
                'Description'=>'Comercio al por menor de libros, periódicos, materiales y artículos de papelería y escritorio en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4762',
                'Description'=>'Comercio al por menor de artículos deportivos, en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4769',
                'Description'=>'Comercio al por menor de otros artículos culturales y de entretenimiento n.c.p. en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4771',
                'Description'=>'Comercio al por menor de prendas de vestir y sus accesorios (incluye artículos de piel en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4772',
                'Description'=>'Comercio al por menor de todo tipo de calzado y artículos de cuero y sucedáneos del cuero en establecimientos especializados.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4773',
                'Description'=>'Comercio al por menor de productos farmacéuticos y medicinales, cosméticos y artículos de tocador en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4774',
                'Description'=>'Comercio al por menor de otros productos nuevos en establecimientos especializados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4775',
                'Description'=>'Comercio al por menor de artículos de segunda mano'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4781',
                'Description'=>'Comercio al por menor de alimentos, bebidas y tabaco, en puestos de venta móviles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4782',
                'Description'=>'Comercio al por menor de productos textiles, prendas de vestir y calzado, en puestos de venta móviles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4789',
                'Description'=>'Comercio al por menor de otros productos en puestos de venta móviles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4791',
                'Description'=>'Comercio al por menor realizado a través de Internet'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4792',
                'Description'=>'Comercio al por menor realizado a través de casas de venta o por correo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4799',
                'Description'=>'Otros tipos de comercio al por menor no realizado en establecimientos, puestos de venta o mercados.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4911',
                'Description'=>'Transporte férreo de pasajeros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4912',
                'Description'=>'Transporte férreo de carga'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4921',
                'Description'=>'Transporte de pasajeros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4922',
                'Description'=>'Transporte mixto'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4923',
                'Description'=>'Transporte de carga por carretera'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'4930',
                'Description'=>'Transporte por tuberías'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5011',
                'Description'=>'Transporte de pasajeros marítimo y de cabotaje'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5012',
                'Description'=>'Transporte de carga marítimo y de cabotaje'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5021',
                'Description'=>'Transporte fluvial de pasajeros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5022',
                'Description'=>'Transporte fluvial de carga'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5111',
                'Description'=>'Transporte aéreo nacional de pasajeros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5112',
                'Description'=>'Transporte aéreo internacional de pasajeros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5121',
                'Description'=>'Transporte aéreo nacional de carga'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5122',
                'Description'=>'Transporte aéreo internacional de carga'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5210',
                'Description'=>'Almacenamiento y depósito'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5221',
                'Description'=>'Actividades de estaciones, vías y servicios complementarios para el transporte terrestre'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5222',
                'Description'=>'Actividades de puertos y servicios complementarios para el transporte acuático'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5223',
                'Description'=>'Actividades de aeropuertos, servicios de navegación aérea y demás actividades conexas al transporte aéreo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5224',
                'Description'=>'Manipulación de carga'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5229',
                'Description'=>'Otras actividades complementarias al transporte'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5310',
                'Description'=>'Actividades postales nacionales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5320',
                'Description'=>'Actividades de mensajería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5511',
                'Description'=>'Alojamiento en hoteles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5512',
                'Description'=>'Alojamiento en apartahoteles'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5513',
                'Description'=>'Alojamiento en centros vacacionales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5514',
                'Description'=>'Alojamiento rural'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5519',
                'Description'=>'Otros tipos de alojamientos para visitantes'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5520',
                'Description'=>'Actividades de zonas de camping y parques para vehículos recreacionales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5530',
                'Description'=>'Servicio por horas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5590',
                'Description'=>'Otros tipos de alojamiento n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5611',
                'Description'=>'Expendio a la mesa de comidas preparadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5612',
                'Description'=>'Expendio por autoservicio de comidas preparadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5613',
                'Description'=>'Expendio de comidas preparadas en cafeterías'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5619',
                'Description'=>'Otros tipos de expendio de comidas preparadas n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5621',
                'Description'=>'Catering para eventos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5629',
                'Description'=>'Actividades de otros servicios de comidas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5630',
                'Description'=>'Expendio de bebidas alcohólicas para el consumo dentro del establecimiento'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5811',
                'Description'=>'Edición de libros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5812',
                'Description'=>'Edición de directorios y listas de correo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5813',
                'Description'=>'Edición de periódicos, revistas y otras publicaciones periódicas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5819',
                'Description'=>'Otros trabajos de edición'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5820',
                'Description'=>'Edición de programas de informática (software)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5911',
                'Description'=>'Actividades de producción de películas cinematográficas, videos, programas, anuncios y comerciales de televisión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5912',
                'Description'=>'Actividades de posproducción de películas cinematográficas, videos, programas, anuncios y comerciales de televisión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5913',
                'Description'=>'Actividades de distribución de películas cinematográficas, videos, programas, anuncios y comerciales de televisión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5914',
                'Description'=>'Actividades de exhibición de películas cinematográficas y videos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'5920',
                'Description'=>'Actividades de grabación de sonido y edición de música'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6010',
                'Description'=>'Actividades de programación y transmisión en el servicio de radiodifusión sonora'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6020',
                'Description'=>'Actividades de programación y transmisión de televisión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6110',
                'Description'=>'Actividades de telecomunicaciones alámbricas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6120',
                'Description'=>'Actividades de telecomunicaciones inalámbricas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6130',
                'Description'=>'Actividades de telecomunicación satelital'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6190',
                'Description'=>'Otras actividades de telecomunicaciones'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6201',
                'Description'=>'Actividades de desarrollo de sistemas informáticos (planificación, análisis, diseño, programación, pruebas)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6202',
                'Description'=>'Actividades de consultoría informática y actividades de administración de instalaciones informáticas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6209',
                'Description'=>'Otras actividades de tecnologías de información y actividades de servicios informáticos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6311',
                'Description'=>'Procesamiento de datos, alojamiento (hosting) y actividades relacionadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6312',
                'Description'=>'Portales web'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6391',
                'Description'=>'Actividades de agencias de noticias'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6399',
                'Description'=>'Otras actividades de servicio de información n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6411',
                'Description'=>'Banco Central'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6412',
                'Description'=>'Bancos comerciales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6421',
                'Description'=>'Actividades de las corporaciones financieras'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6422',
                'Description'=>'Actividades de las compañías de financiamiento'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6423',
                'Description'=>'Banca de segundo piso'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6424',
                'Description'=>'Actividades de las cooperativas financieras'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6431',
                'Description'=>'Fideicomisos, fondos y entidades financieras similares'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6432',
                'Description'=>'Fondos de cesantías'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6491',
                'Description'=>'Leasing financiero (arrendamiento financiero)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6492',
                'Description'=>'Actividades financieras de fondos de empleados y otras formas asociativas del sector solidario'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6493',
                'Description'=>'Actividades de compra de cartera o factoring'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6494',
                'Description'=>'Otras actividades de distribución de fondos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6495',
                'Description'=>'Instituciones especiales oficiales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6499',
                'Description'=>'Otras actividades de servicio financiero, excepto las de seguros y pensiones n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6511',
                'Description'=>'Seguros generales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6512',
                'Description'=>'Seguros de vida'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6513',
                'Description'=>'Reaseguros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6514',
                'Description'=>'Capitalización'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6521',
                'Description'=>'Servicios de seguros sociales de salud'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6522',
                'Description'=>'Servicios de seguros sociales de riesgos profesionales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6531',
                'Description'=>'Régimen de prima media con prestación definida (RPM)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6532',
                'Description'=>'Régimen de ahorro individual (RAI)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6611',
                'Description'=>'Administración de mercados financieros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6612',
                'Description'=>'Corretaje de valores y de contratos de productos básicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6613',
                'Description'=>'Otras actividades relacionadas con el mercado de valores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6614',
                'Description'=>'Actividades de las casas de cambio'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6615',
                'Description'=>'Actividades de los profesionales de compra y venta de divisas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6619',
                'Description'=>'Otras actividades auxiliares de las actividades de servicios financieros n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6621',
                'Description'=>'Actividades de agentes y corredores de seguros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6629',
                'Description'=>'Evaluación de riesgos y daños, y otras actividades de servicios auxiliares'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6630',
                'Description'=>'Actividades de administración de fondos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6810',
                'Description'=>'Actividades inmobiliarias realizadas con bienes propios o arrendados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6820',
                'Description'=>'Actividades inmobiliarias realizadas a cambio de una retribución o por contrata'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6910',
                'Description'=>'Actividades jurídicas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'6920',
                'Description'=>'Actividades de contabilidad, teneduría de libros, auditoría financiera y asesoría tributaria'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7010',
                'Description'=>'Actividades de administración empresarial'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7020',
                'Description'=>'Actividades de consultaría de gestión'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7110',
                'Description'=>'Actividades de arquitectura e ingeniería y otras actividades conexas de consultoría técnica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7120',
                'Description'=>'Ensayos y análisis técnicos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7210',
                'Description'=>'Investigaciones y desarrollo experimental en el campo de las ciencias naturales y la ingeniería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7220',
                'Description'=>'Investigaciones y desarrollo experimental en el campo de las ciencias sociales y las humanidades'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7310',
                'Description'=>'Publicidad'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7320',
                'Description'=>'Estudios de mercado y realización de encuestas de opinión pública'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7410',
                'Description'=>'Actividades especializadas de diseño'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7420',
                'Description'=>'Actividades de fotografía'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7490',
                'Description'=>'Otras actividades profesionales, científicas y técnicas n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7500',
                'Description'=>'Actividades veterinarias'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7710',
                'Description'=>'Alquiler y arrendamiento de vehículos automotores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7721',
                'Description'=>'Alquiler y arrendamiento de equipo recreativo y deportivo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7722',
                'Description'=>'Alquiler de videos y discos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7729',
                'Description'=>'Alquiler y arrendamiento de otros efectos personales y enseres domésticos n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7730',
                'Description'=>'Alquiler y arrendamiento de otros tipos de maquinaria, equipo y bienes tangibles n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7740',
                'Description'=>'Arrendamiento de propiedad intelectual y productos similares, excepto obras protegidas por derechos de autor'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7810',
                'Description'=>'Actividades de agencias de empleo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7820',
                'Description'=>'Actividades de agencias de empleo temporal'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7830',
                'Description'=>'Otras actividades de suministro de recurso humano'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7911',
                'Description'=>'Actividades de las agencias de viaje'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7912',
                'Description'=>'Actividades de operadores turísticos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'7990',
                'Description'=>'Otros servicios de reserva y actividades relacionadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8010',
                'Description'=>'Actividades de seguridad privada'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8020',
                'Description'=>'Actividades de servicios de sistemas de seguridad'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8030',
                'Description'=>'Actividades de detectives e investigadores privados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8110',
                'Description'=>'Actividades combinadas de apoyo a instalaciones'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8121',
                'Description'=>'Limpieza general interior de edificios'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8129',
                'Description'=>'Otras actividades de limpieza de edificios e instalaciones industriales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8130',
                'Description'=>'Actividades de paisajismo y servicios de mantenimiento conexos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8211',
                'Description'=>'Actividades combinadas de servicios administrativos de oficina'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8219',
                'Description'=>'Fotocopiado, preparación de documentos y otras actividades especializadas de apoyo a oficina'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8220',
                'Description'=>'Actividades de centros de llamadas (Call center)'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8230',
                'Description'=>'Organización de convenciones y eventos comerciales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8291',
                'Description'=>'Actividades de agencias de cobranza y oficinas de calificación crediticia'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8292',
                'Description'=>'Actividades de envase y empaque'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8299',
                'Description'=>'Otras actividades de servicio de apoyo a las empresas n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8411',
                'Description'=>'Actividades legislativas de la administración pública'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8412',
                'Description'=>'Actividades ejecutivas de la administración pública'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8413',
                'Description'=>'Regulación de las actividades de organismos que prestan servicios de salud, educativos culturales y otros servicios sociales, excepto servicios de seguridad social'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8414',
                'Description'=>'Actividades reguladoras y facilitadoras de la actividad económica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8415',
                'Description'=>'Actividades de los otros órganos de control'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8421',
                'Description'=>'Relaciones exteriores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8422',
                'Description'=>'Actividades de defensa'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8423',
                'Description'=>'Orden público y actividades de seguridad'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8424',
                'Description'=>'Administración de justicia'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8430',
                'Description'=>'Actividades de planes de seguridad social de afiliación obligatoria'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8511',
                'Description'=>'Educación de la primera infancia'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8512',
                'Description'=>'Educación preescolar'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8513',
                'Description'=>'Educación básica primaria'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8521',
                'Description'=>'Educación básica secundaria'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8522',
                'Description'=>'Educación media académica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8523',
                'Description'=>'Educación media técnica y de formación laboral'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8530',
                'Description'=>'Establecimientos que combinan diferentes niveles de educación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8541',
                'Description'=>'Educación técnica profesional'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8542',
                'Description'=>'Educación tecnológica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8543',
                'Description'=>'Educación de instituciones universitarias o de escuelas tecnológicas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8544',
                'Description'=>'Educación de universidades'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8551',
                'Description'=>'Formación académica no formal'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8552',
                'Description'=>'Enseñanza deportiva y recreativa'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8553',
                'Description'=>'Enseñanza cultural'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8559',
                'Description'=>'Otros tipos de educación n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8560',
                'Description'=>'Actividades de apoyo a la educación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8610',
                'Description'=>'Actividades de hospitales y clínicas, con internación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8621',
                'Description'=>'Actividades de la práctica médica, sin internación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8622',
                'Description'=>'Actividades de la práctica odontológica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8691',
                'Description'=>'Actividades de apoyo diagnóstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8692',
                'Description'=>'Actividades de apoyo terapéutico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8699',
                'Description'=>'Otras actividades de atención de la salud humana'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8710',
                'Description'=>'Actividades de atención residencial medicalizada de tipo general'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8720',
                'Description'=>'Actividades de atención residencial, para el cuidado de pacientes con retardo mental, enfermedad mental y consumo de sustancias psicoactivas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8730',
                'Description'=>'Actividades de atención en instituciones para el cuidado de personas mayores y/o discapacitadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8790',
                'Description'=>'Otras actividades de atención en instituciones con alojamiento'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8810',
                'Description'=>'Actividades de asistencia social sin alojamiento para personas mayores y discapacitadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'8890',
                'Description'=>'Otras actividades de asistencia social sin alojamiento'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9001',
                'Description'=>'Creación literaria'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9002',
                'Description'=>'Creación musical'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9003',
                'Description'=>'Creación teatral'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9004',
                'Description'=>'Creación audiovisual'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9005',
                'Description'=>'Artes plásticas y visuales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9006',
                'Description'=>'Actividades teatrales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9007',
                'Description'=>'Actividades de espectáculos musicales en vivo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9008',
                'Description'=>'Otras actividades de espectáculos en vivo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9101',
                'Description'=>'Actividades de bibliotecas y archivos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9102',
                'Description'=>'Actividades y funcionamiento de museos, conservación de edificios y sitios históricos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9103',
                'Description'=>'Actividades de jardines botánicos, zoológicos y reservas naturales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9200',
                'Description'=>'Actividades de juegos de azar y apuestas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9311',
                'Description'=>'Gestión de instalaciones deportivas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9312',
                'Description'=>'Actividades de clubes deportivos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9319',
                'Description'=>'Otras actividades deportivas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9321',
                'Description'=>'Actividades de parques de atracciones y parques temáticos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9329',
                'Description'=>'Otras actividades recreativas y de esparcimiento n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9411',
                'Description'=>'Actividades de asociaciones empresariales y de empleadores'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9412',
                'Description'=>'Actividades de asociaciones profesionales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9420',
                'Description'=>'Actividades de sindicatos de empleados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9491',
                'Description'=>'Actividades de asociaciones religiosas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9492',
                'Description'=>'Actividades de asociaciones políticas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9499',
                'Description'=>'Actividades de otras asociaciones n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9511',
                'Description'=>'Mantenimiento y reparación de computadores y de equipo periférico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9512',
                'Description'=>'Mantenimiento y reparación de equipos de comunicación'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9521',
                'Description'=>'Mantenimiento y reparación de aparatos electrónicos de consumo'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9522',
                'Description'=>'Mantenimiento y reparación de aparatos y equipos domésticos y de jardinería'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9523',
                'Description'=>'Reparación de calzado y artículos de cuero'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9524',
                'Description'=>'Reparación de muebles y accesorios para el hogar'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9529',
                'Description'=>'Mantenimiento y reparación de otros efectos personales y enseres domésticos'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9601',
                'Description'=>'Lavado y limpieza, incluso la limpieza en seco, de productos textiles y de piel'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9602',
                'Description'=>'Peluquería y otros tratamientos de belleza'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9603',
                'Description'=>'Pompas fúnebres y actividades relacionadas'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9609',
                'Description'=>'Otras actividades de servicios personales n.c.p.'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9700',
                'Description'=>'Actividades de los hogares individuales como empleadores de personal doméstico'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9810',
                'Description'=>'Actividades no diferenciadas de los hogares individuales como productores de bienes para uso propio'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9820',
                'Description'=>'Actividades no diferenciadas de los hogares individuales como productores de servicios para uso propio'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'9900',
                'Description'=>'Actividades de organizaciones y entidades extraterritoriales'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0010',
                'Description'=>'Asalariados'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0081',
                'Description'=>'Personas Naturales sin Actividad Económica'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0082',
                'Description'=>'Personas Naturales Subsidiadas por Terceros'
            ]
        );
        DB::table('economicactivity')->insert(
            [
                'cod'=>'0090',
                'Description'=>'Rentistas de Capital, solo para personas naturales'
            ]
        );
        
    }
}
