<?php
/**
 * Dummy product definitions for seed-dummy-data.php.
 * Returns array of 12 B2B industrial paint products.
 * Taxonomy slugs match inc/db/seed-taxonomy-terms.php.
 *
 * @package Alkana
 */
defined( 'ABSPATH' ) || exit;

return [
	[
		'title' => 'Alkana Epoxy Primer EP-10', 'name_vi' => 'Sơn lót Epoxy Alkana EP-10', 'sku' => 'ALK-EP-10',
		'excerpt' => 'Sơn lót epoxy 2 thành phần, bám dính vượt trội trên bề mặt kim loại và bê tông, chống rỉ hiệu quả.',
		'content' => '<p>Alkana Epoxy Primer EP-10 là dòng sơn lót epoxy hai thành phần cao cấp, được thiết kế đặc biệt cho các ứng dụng công nghiệp nặng. Sản phẩm tạo lớp nền bám dính xuất sắc, chống ẩm và chống rỉ hiệu quả trên bề mặt thép, sắt và bê tông.</p><p>Phù hợp cho nhà xưởng, kết cấu thép, bồn chứa và các công trình đòi hỏi tiêu chuẩn bảo vệ cao.</p>',
		'coverage' => '8 – 10 m²/lít/lớp', 'mix' => '4:1 theo trọng lượng (base:hardener)', 'thinner' => 'Alkana Thinner No.5 (10–15%)', 'layer' => 'primer',
		'dry_touch' => '2 giờ (tại 30°C)', 'dry_hard' => '8 giờ (tại 30°C)', 'dry_recoat' => '6 – 24 giờ',
		'cat' => 'epoxy-coating', 'surface' => [ 'metal-steel', 'concrete' ], 'system' => 'epoxy-2k', 'gloss' => 'matte', 'featured' => true,
	],
	[
		'title' => 'Alkana PU TopCoat Pro TC-20', 'name_vi' => 'Sơn phủ PU Alkana TC-20', 'sku' => 'ALK-TC-20',
		'excerpt' => 'Sơn phủ polyurethane 2K bóng cao, chống UV và hóa chất, bảo vệ kết cấu thép ngoài trời.',
		'content' => '<p>Alkana PU TopCoat Pro TC-20 là lớp sơn phủ polyurethane hai thành phần với độ bóng cao vượt trội. Khả năng chống tia UV xuất sắc giúp giữ màu bền lâu trong điều kiện ngoài trời khắc nghiệt.</p><p>Ứng dụng lý tưởng cho cầu thép, kết cấu ngoài trời, thiết bị công nghiệp và bồn chứa hóa chất.</p>',
		'coverage' => '10 – 12 m²/lít/lớp', 'mix' => '5:1 theo trọng lượng', 'thinner' => 'Alkana PU Thinner No.7 (5–10%)', 'layer' => 'topcoat',
		'dry_touch' => '45 phút (tại 30°C)', 'dry_hard' => '6 giờ (tại 30°C)', 'dry_recoat' => '4 – 16 giờ',
		'cat' => 'polyurethane-coating', 'surface' => [ 'metal-steel' ], 'system' => 'pu-2k', 'gloss' => 'high-gloss', 'featured' => true,
	],
	[
		'title' => 'Alkana WaterGuard 500', 'name_vi' => 'Chống thấm Alkana WaterGuard 500', 'sku' => 'ALK-WG-500',
		'excerpt' => 'Chống thấm acrylic đàn hồi cao, bảo vệ mái và tường trước mưa nắng khắc nghiệt.',
		'content' => '<p>Alkana WaterGuard 500 là hệ chống thấm acrylic đàn hồi một thành phần, dễ thi công bằng cọ lăn hoặc phun. Độ đàn hồi cao giúp phủ kín các vết nứt nhỏ, chống thấm tuyệt đối cho mái bê tông, tường ngoại thất.</p><p>Khô nhanh, an toàn với môi trường, sử dụng nước làm dung môi pha loãng.</p>',
		'coverage' => '3 – 4 m²/lít/lớp', 'mix' => 'Sử dụng ngay (Ready to use)', 'thinner' => 'Nước sạch (5–10%)', 'layer' => 'sealer',
		'dry_touch' => '1 giờ (tại 30°C)', 'dry_hard' => '4 giờ (tại 30°C)', 'dry_recoat' => '2 – 4 giờ',
		'cat' => 'roof-waterproofing', 'surface' => [ 'concrete', 'plaster-wall' ], 'system' => 'acrylic-waterproof', 'gloss' => 'matte', 'featured' => true,
	],
	[
		'title' => 'Alkana FloorShield EF-300', 'name_vi' => 'Sơn sàn Epoxy Alkana EF-300', 'sku' => 'ALK-EF-300',
		'excerpt' => 'Sơn sàn epoxy tự phẳng, chịu mài mòn cao cho nhà xưởng và bãi đỗ xe.',
		'content' => '<p>Alkana FloorShield EF-300 là hệ sơn sàn epoxy hai thành phần tự phẳng, tạo bề mặt bóng đều, chịu mài mòn và hóa chất. Thiết kế cho sàn nhà xưởng sản xuất, kho bãi, bãi đỗ xe ngầm.</p><p>Dễ vệ sinh, chống bụi, đạt tiêu chuẩn an toàn thực phẩm cho nhà máy chế biến.</p>',
		'coverage' => '5 – 7 m²/lít/lớp', 'mix' => '3:1 theo trọng lượng', 'thinner' => 'Alkana Thinner No.5 (max 5%)', 'layer' => 'topcoat',
		'dry_touch' => '4 giờ (tại 30°C)', 'dry_hard' => '24 giờ (tại 30°C)', 'dry_recoat' => '12 – 24 giờ',
		'cat' => 'floor-epoxy', 'surface' => [ 'concrete-floor' ], 'system' => 'epoxy-2k', 'gloss' => 'semi-gloss', 'featured' => true,
	],
	[
		'title' => 'Alkana WoodVarnish Clear WC-01', 'name_vi' => 'Vecni gỗ trong suốt Alkana WC-01', 'sku' => 'ALK-WC-01',
		'excerpt' => 'Vecni PU trong suốt 2K, tôn vinh vân gỗ tự nhiên, chống trầy và chịu nước tốt.',
		'content' => '<p>Alkana WoodVarnish Clear WC-01 là vecni polyurethane hai thành phần trong suốt, giữ nguyên vẻ đẹp tự nhiên của vân gỗ. Tạo lớp phủ cứng, chống trầy xước, chịu nước và hóa chất gia dụng.</p><p>Ứng dụng cho đồ gỗ nội thất, cửa gỗ, sàn gỗ và ván ép.</p>',
		'coverage' => '12 – 14 m²/lít/lớp', 'mix' => '10:1 theo trọng lượng', 'thinner' => 'Alkana Wood Thinner No.2 (10–20%)', 'layer' => 'topcoat',
		'dry_touch' => '30 phút (tại 25°C)', 'dry_hard' => '3 giờ (tại 25°C)', 'dry_recoat' => '2 – 4 giờ',
		'cat' => 'wood-varnish-clear', 'surface' => [ 'wood' ], 'system' => 'pu-2k', 'gloss' => 'gloss', 'featured' => true,
	],
	[
		'title' => 'Alkana Anti-Rust AR-15', 'name_vi' => 'Sơn chống rỉ Alkana AR-15', 'sku' => 'ALK-AR-15',
		'excerpt' => 'Sơn lót chống rỉ alkyd biến tính, bảo vệ kim loại đen lên đến 5 năm trong điều kiện nhiệt đới.',
		'content' => '<p>Alkana Anti-Rust AR-15 là sơn lót chống rỉ một thành phần gốc alkyd biến tính với bột kẽm phosphate. Bám dính tốt trên thép mới và thép đã xử lý bề mặt, khô nhanh, dễ phủ lớp tiếp theo.</p><p>Phù hợp cho hàng rào, cổng sắt, khung kèo thép, lan can và thiết bị cơ khí.</p>',
		'coverage' => '10 – 12 m²/lít/lớp', 'mix' => 'Sử dụng ngay (khuấy đều trước khi dùng)', 'thinner' => 'Alkana Thinner No.3 (5–10%)', 'layer' => 'primer',
		'dry_touch' => '1 giờ (tại 30°C)', 'dry_hard' => '6 giờ (tại 30°C)', 'dry_recoat' => '4 – 24 giờ',
		'cat' => 'anti-rust', 'surface' => [ 'metal-steel' ], 'system' => 'alkyd', 'gloss' => 'satin', 'featured' => true,
	],
	[
		'title' => 'Alkana Interior Silk IS-100', 'name_vi' => 'Sơn nội thất Alkana IS-100', 'sku' => 'ALK-IS-100',
		'excerpt' => 'Sơn nội thất cao cấp gốc nước, phủ mịn, chống nấm mốc, an toàn sức khỏe.',
		'content' => '<p>Alkana Interior Silk IS-100 là sơn nội thất cao cấp gốc nước acrylic, tạo bề mặt mịn như lụa. Công thức không chứa chì, thủy ngân, VOC thấp, an toàn cho phòng ngủ và phòng trẻ em.</p><p>Kháng nấm mốc, dễ lau chùi, giữ màu bền đẹp suốt thời gian sử dụng.</p>',
		'coverage' => '10 – 12 m²/lít/lớp', 'mix' => 'Sử dụng ngay', 'thinner' => 'Nước sạch (5–10%)', 'layer' => 'topcoat',
		'dry_touch' => '30 phút (tại 30°C)', 'dry_hard' => '2 giờ (tại 30°C)', 'dry_recoat' => '2 – 4 giờ',
		'cat' => 'interior-paint', 'surface' => [ 'plaster-wall', 'concrete' ], 'system' => 'water-based', 'gloss' => 'satin', 'featured' => false,
	],
	[
		'title' => 'Alkana Exterior Shield ES-200', 'name_vi' => 'Sơn ngoại thất Alkana ES-200', 'sku' => 'ALK-ES-200',
		'excerpt' => 'Sơn ngoại thất acrylic chống nắng mưa, tự làm sạch, giữ màu bền trên 7 năm.',
		'content' => '<p>Alkana Exterior Shield ES-200 là sơn phủ ngoại thất acrylic 100% cao cấp với công nghệ tự làm sạch Lotus Effect. Chống kiềm, chống tảo rêu, bền màu trước tia UV khắc nghiệt nhiệt đới.</p><p>Lý tưởng cho mặt tiền nhà phố, biệt thự, chung cư và công trình thương mại.</p>',
		'coverage' => '10 – 14 m²/lít/lớp', 'mix' => 'Sử dụng ngay', 'thinner' => 'Nước sạch (5–10%)', 'layer' => 'topcoat',
		'dry_touch' => '30 phút (tại 30°C)', 'dry_hard' => '2 giờ (tại 30°C)', 'dry_recoat' => '2 – 4 giờ',
		'cat' => 'exterior-paint', 'surface' => [ 'plaster-wall' ], 'system' => 'water-based', 'gloss' => 'semi-gloss', 'featured' => false,
	],
	[
		'title' => 'Alkana Tank Seal TS-400', 'name_vi' => 'Chống thấm bể chứa Alkana TS-400', 'sku' => 'ALK-TS-400',
		'excerpt' => 'Epoxy chống thấm chuyên dụng cho bể nước, hồ bơi, bể xử lý nước thải.',
		'content' => '<p>Alkana Tank Seal TS-400 là hệ epoxy chống thấm hai thành phần không dung môi, chuyên dụng cho các bể chứa nước sinh hoạt, hồ bơi, bể xử lý nước thải công nghiệp.</p><p>Đạt chứng nhận an toàn thực phẩm, chịu ngâm nước liên tục, kháng hóa chất mạnh.</p>',
		'coverage' => '4 – 6 m²/lít/lớp', 'mix' => '2:1 theo trọng lượng', 'thinner' => 'Không pha loãng (solventless)', 'layer' => 'sealer',
		'dry_touch' => '6 giờ (tại 30°C)', 'dry_hard' => '24 giờ (tại 30°C)', 'dry_recoat' => '12 – 24 giờ',
		'cat' => 'floor-tank-waterproofing', 'surface' => [ 'water-tank', 'concrete' ], 'system' => 'epoxy-2k', 'gloss' => 'gloss', 'featured' => false,
	],
	[
		'title' => 'Alkana Alkyd Enamel AE-50', 'name_vi' => 'Sơn dầu Alkyd Alkana AE-50', 'sku' => 'ALK-AE-50',
		'excerpt' => 'Sơn dầu alkyd bóng cao, phủ đều, thi công dễ dàng bằng cọ hoặc phun.',
		'content' => '<p>Alkana Alkyd Enamel AE-50 là sơn phủ gốc dầu alkyd bóng cao truyền thống, phủ đều một lớp, thi công linh hoạt bằng cọ, lăn hoặc phun không khí.</p><p>Phù hợp cho cửa sắt, lan can, thiết bị cơ khí, máy móc công nghiệp cần lớp phủ bóng bề mặt.</p>',
		'coverage' => '12 – 14 m²/lít/lớp', 'mix' => 'Sử dụng ngay (khuấy đều)', 'thinner' => 'Alkana Thinner No.3 (5–15%)', 'layer' => 'topcoat',
		'dry_touch' => '2 giờ (tại 30°C)', 'dry_hard' => '12 giờ (tại 30°C)', 'dry_recoat' => '16 – 24 giờ',
		'cat' => 'alkyd-oil-paint', 'surface' => [ 'metal-steel' ], 'system' => 'alkyd', 'gloss' => 'high-gloss', 'featured' => false,
	],
	[
		'title' => 'Alkana Floor PU FP-350', 'name_vi' => 'Sơn sàn PU Alkana FP-350', 'sku' => 'ALK-FP-350',
		'excerpt' => 'Sơn sàn polyurethane chống trơn trượt, chịu mài mòn cao cho bãi đỗ xe và ramp dốc.',
		'content' => '<p>Alkana Floor PU FP-350 là sơn sàn polyurethane hai thành phần với hạt chống trơn trượt tích hợp. Độ cứng và đàn hồi vượt trội, chịu va đập bánh xe nâng, xe tải hạng nặng.</p><p>Thiết kế cho bãi đỗ xe ngầm, ramp dốc, sàn kho lạnh và khu vực có nước.</p>',
		'coverage' => '6 – 8 m²/lít/lớp', 'mix' => '4:1 theo trọng lượng', 'thinner' => 'Alkana PU Thinner No.7 (5–10%)', 'layer' => 'topcoat',
		'dry_touch' => '3 giờ (tại 30°C)', 'dry_hard' => '12 giờ (tại 30°C)', 'dry_recoat' => '8 – 24 giờ',
		'cat' => 'floor-pu', 'surface' => [ 'concrete-floor' ], 'system' => 'pu-2k', 'gloss' => 'anti-slip', 'featured' => false,
	],
	[
		'title' => 'Alkana TileBond TB-01', 'name_vi' => 'Keo dán gạch Alkana TB-01', 'sku' => 'ALK-TB-01',
		'excerpt' => 'Keo dán gạch xi măng polymer, bám dính cực mạnh, chống thấm và chống nứt.',
		'content' => '<p>Alkana TileBond TB-01 là vữa dán gạch xi măng polymer một thành phần, pha trộn sẵn khô, chỉ cần thêm nước. Bám dính cực mạnh trên bê tông, xi măng, gạch cũ, phù hợp gạch lớn và gạch porcelain.</p><p>Ứng dụng cho ốp lát sàn tường nội ngoại thất, hồ bơi, khu vực ẩm ướt.</p>',
		'coverage' => '4 – 5 m²/bao 25kg', 'mix' => 'Trộn với 6–7 lít nước/bao 25kg', 'thinner' => 'Nước sạch', 'layer' => 'filler',
		'dry_touch' => '3 giờ (tại 30°C)', 'dry_hard' => '24 giờ (tại 30°C)', 'dry_recoat' => 'Không áp dụng',
		'cat' => 'tile-adhesive', 'surface' => [ 'concrete', 'plaster-wall' ], 'system' => 'single-component', 'gloss' => 'matte', 'featured' => false,
	],
];
