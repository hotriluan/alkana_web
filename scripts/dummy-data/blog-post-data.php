<?php
/**
 * Blog post sample data for Alkana B2B website.
 * Returns array of 6 posts across 3 categories.
 *
 * @package Alkana
 */

return [
	// ── Category: Kiến thức Kỹ thuật ──
	[
		'title'    => 'Hướng dẫn chọn sơn Epoxy phù hợp cho nhà xưởng công nghiệp',
		'slug'     => 'huong-dan-chon-son-epoxy-nha-xuong',
		'category' => 'kien-thuc-ky-thuat',
		'excerpt'  => 'Phân tích các yếu tố quan trọng khi lựa chọn sơn epoxy: tải trọng sàn, điều kiện hóa chất, và yêu cầu vệ sinh công nghiệp.',
		'content'  => '<h2>Tại sao chọn đúng loại Epoxy là quan trọng?</h2>
<p>Sơn epoxy là giải pháp bảo vệ bề mặt hàng đầu cho nhà xưởng công nghiệp. Tuy nhiên, việc chọn sai loại có thể dẫn đến bong tróc, phồng rộp, và chi phí sửa chữa gấp 3 lần đầu tư ban đầu.</p>
<h3>Các yếu tố quyết định</h3>
<ul>
<li><strong>Tải trọng sàn</strong>: Xe nâng, xe tải nặng yêu cầu epoxy self-leveling dày 3-5mm</li>
<li><strong>Tiếp xúc hóa chất</strong>: Axit, kiềm, dung môi — chọn epoxy novolac hoặc vinyl ester</li>
<li><strong>Nhiệt độ vận hành</strong>: Trên 60°C cần epoxy chịu nhiệt đặc biệt</li>
<li><strong>Yêu cầu vệ sinh</strong>: Ngành thực phẩm, dược phẩm cần bề mặt seamless, kháng khuẩn</li>
</ul>
<h3>Quy trình thi công chuẩn</h3>
<p>Bề mặt bê tông cần được chuẩn bị đúng cách: mài bóng, xử lý dầu mỡ, và kiểm tra độ ẩm (<4%). Lớp primer epoxy phải đạt độ bám dính tối thiểu 1.5 MPa trước khi thi công topcoat.</p>',
		'image'    => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&h=450&fit=crop',
	],
	[
		'title'    => 'So sánh hệ thống sơn Polyurethane và Epoxy: Khi nào dùng loại nào?',
		'slug'     => 'so-sanh-polyurethane-va-epoxy',
		'category' => 'kien-thuc-ky-thuat',
		'excerpt'  => 'Phân tích ưu nhược điểm của PU vs Epoxy trong các môi trường công nghiệp khác nhau.',
		'content'  => '<h2>Epoxy hay Polyurethane?</h2>
<p>Đây là câu hỏi phổ biến nhất từ khách hàng B2B. Câu trả lời phụ thuộc vào điều kiện môi trường và yêu cầu kỹ thuật cụ thể.</p>
<h3>Epoxy — Vua của độ bám dính</h3>
<ul>
<li>Độ cứng cao, chịu mài mòn tốt</li>
<li>Khả năng bám dính xuất sắc trên bê tông và thép</li>
<li>Nhược điểm: biến vàng dưới tia UV, không phù hợp ngoài trời</li>
</ul>
<h3>Polyurethane — Bền màu, dẻo dai</h3>
<ul>
<li>Giữ màu và bóng lâu dài dưới ánh nắng</li>
<li>Linh hoạt hơn, chịu va đập tốt</li>
<li>Thường dùng làm topcoat trên lớp epoxy primer</li>
</ul>
<p><strong>Khuyến nghị:</strong> Hệ thống kết hợp Epoxy primer + PU topcoat cho hiệu quả tối ưu.</p>',
		'image'    => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800&h=450&fit=crop',
	],
	// ── Category: Tin tức Công ty ──
	[
		'title'    => 'Alkana Coating mở rộng nhà máy sản xuất tại Bình Dương',
		'slug'     => 'alkana-mo-rong-nha-may-binh-duong',
		'category' => 'tin-tuc-cong-ty',
		'excerpt'  => 'Nhà máy mới có công suất 10.000 tấn/năm, trang bị dây chuyền tự động hóa hiện đại từ Đức.',
		'content'  => '<h2>Mở rộng quy mô sản xuất</h2>
<p>Alkana Coating chính thức khánh thành nhà máy sản xuất mới tại KCN VSIP III, Bình Dương, nâng tổng công suất lên 10.000 tấn sơn công nghiệp mỗi năm.</p>
<h3>Điểm nổi bật</h3>
<ul>
<li>Dây chuyền sản xuất tự động hóa nhập khẩu từ Đức</li>
<li>Hệ thống kiểm soát chất lượng theo tiêu chuẩn ISO 9001:2015</li>
<li>Phòng R&D với thiết bị kiểm tra bám dính, độ bền thời tiết, và chống ăn mòn</li>
<li>Kho chứa nguyên liệu đạt chuẩn PCCC</li>
</ul>
<p>Với việc mở rộng này, Alkana cam kết rút ngắn thời gian giao hàng xuống 3-5 ngày làm việc cho khu vực miền Nam.</p>',
		'image'    => 'https://images.unsplash.com/photo-1513828583688-c52646db42da?w=800&h=450&fit=crop',
	],
	[
		'title'    => 'Alkana đạt chứng nhận ISO 14001:2015 về Quản lý Môi trường',
		'slug'     => 'alkana-dat-chung-nhan-iso-14001',
		'category' => 'tin-tuc-cong-ty',
		'excerpt'  => 'Cam kết phát triển bền vững với quy trình sản xuất xanh và sản phẩm thân thiện môi trường.',
		'content'  => '<h2>Phát triển bền vững</h2>
<p>Alkana Coating tự hào được cấp chứng nhận ISO 14001:2015 cho hệ thống quản lý môi trường, khẳng định cam kết sản xuất sơn công nghiệp theo tiêu chuẩn xanh.</p>
<h3>Các sáng kiến môi trường</h3>
<ul>
<li>Giảm 40% phát thải VOC bằng công nghệ water-based mới</li>
<li>Hệ thống xử lý nước thải khép kín đạt chuẩn QCVN 40:2011</li>
<li>Tái chế 85% bao bì và phế phẩm sản xuất</li>
<li>Chuyển đổi 30% nguyên liệu sang nguồn gốc sinh học</li>
</ul>',
		'image'    => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=450&fit=crop',
	],
	// ── Category: Case Study ──
	[
		'title'    => 'Case Study: Bảo vệ chống ăn mòn cho Cảng Container Cát Lái',
		'slug'     => 'case-study-chong-an-mon-cang-cat-lai',
		'category' => 'case-study',
		'excerpt'  => 'Hệ thống sơn 3 lớp bảo vệ kết cấu thép tại môi trường biển khắc nghiệt, tuổi thọ dự kiến 15 năm.',
		'content'  => '<h2>Thách thức</h2>
<p>Cảng Container Cát Lái — cảng container lớn nhất miền Nam — cần giải pháp chống ăn mòn cho 32.000 m² kết cấu thép tiếp xúc trực tiếp với khí hậu biển: độ ẩm 85%, nồng độ muối cao, nhiệt độ dao động 25-40°C.</p>
<h3>Giải pháp Alkana</h3>
<ul>
<li><strong>Primer:</strong> Alkana Epoxy Primer EP-10, zinc-rich, 75μm DFT</li>
<li><strong>Intermediate:</strong> Alkana Epoxy MIO, 125μm DFT</li>
<li><strong>Topcoat:</strong> Alkana PU TopCoat TC-20, semi-gloss, 50μm DFT</li>
</ul>
<h3>Kết quả</h3>
<p>Sau 12 tháng kiểm tra, không phát hiện dấu hiệu ăn mòn, bong tróc hoặc phấn hóa. Tuổi thọ dự kiến 15 năm theo ISO 12944-5 category C5-M.</p>',
		'image'    => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800&h=450&fit=crop',
	],
	[
		'title'    => 'Case Study: Sơn sàn Epoxy cho Nhà máy Thực phẩm GMP',
		'slug'     => 'case-study-son-san-epoxy-nha-may-thuc-pham',
		'category' => 'case-study',
		'excerpt'  => 'Giải pháp sàn epoxy self-leveling đạt chuẩn vệ sinh an toàn thực phẩm cho nhà máy 8.500 m².',
		'content'  => '<h2>Thách thức</h2>
<p>Nhà máy chế biến thực phẩm yêu cầu sàn liền mạch, kháng khuẩn, chịu rửa hóa chất hàng ngày, và đạt tiêu chuẩn GMP. Diện tích 8.500 m², thi công trong 15 ngày không ảnh hưởng sản xuất.</p>
<h3>Giải pháp Alkana</h3>
<ul>
<li><strong>System:</strong> Alkana FloorShield EF-300 self-leveling, 3mm tổng chiều dày</li>
<li><strong>Primer:</strong> Alkana Epoxy Primer, moisture-tolerant</li>
<li><strong>Topcoat:</strong> Antimicrobial additive, R11 slip-resistance</li>
<li><strong>Cove base:</strong> Thi công viền bo tường 100mm liền mạch</li>
</ul>
<h3>Kết quả</h3>
<p>Hoàn thành đúng tiến độ 15 ngày. Sàn đạt kiểm tra vệ sinh ATTP lần đầu, không cần sửa chữa. Bảo hành 5 năm toàn diện.</p>',
		'image'    => 'https://images.unsplash.com/photo-1565008447742-97f6f38c985c?w=800&h=450&fit=crop',
	],
];
