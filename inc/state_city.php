<?php
/**
 * Developer : MahdiY
 * Web Site  : MahdiY.IR
 * E-Mail    : M@hdiY.IR
 */

function PWS_get_states(){
	return array(
		'AW' => 'آذربایجان غربی',
		'AE' => 'آذربایجان شرقی',
		'IS' => 'اصفهان',
		'AL' => 'البرز',
		'TE' => 'تهران',
		'KV' => 'خراسان رضوی',
		'KZ' => 'خوزستان',
		'FA' => 'فارس',
		'MN' => 'مازندران',
		'ZA' => 'زنجان',
		'AR' => 'اردبيل',
		'IL' => 'ايلام',
		'BU' => 'بوشهر',
		'CM' => 'چهار محال بختیاری',
		'KS' => 'خراسان شمالی',
		'KJ' => 'خراسان جنوبی',
		'SM' => 'سمنان',
		'SB' => 'سیستان و بلوچستان',
		'QZ' => 'قزوين',
		'QM' => 'قم',
		'KD' => 'کردستان',
		'KE' => 'کرمان',
		'BK' => 'کرمانشاه',
		'KB' => 'کهگیلویه و بویراحمد',
		'GO' => 'گلستان',
		'GI' => 'گیلان',
		'LO' => 'لرستان',
		'MK' => 'مرکزی',
		'HG' => 'هرمزگان',
		'HD' => 'همدان',
		'YA' => 'يزد'
	);
}

function PWS_get_state_city( $state ){
	$states_city = array(
		"AR" => array(
			"0" => "اردبیل",
			"1" => "اندبیل",
			"2" => "آبی‌بیگلو",
			"3" => "بیله‌سوار",
			"4" => "پارس‌آباد",
			"5" => "جعفرآباد",
			"6" => "جنگل فندقلو",
			"7" => "خلخال",
			"8" => "عنبران",
			"9" => "گرمی",
			"10" => "گیلوان",
			"11" => "گیوی",
			"12" => "مشگین‌شهر",
			"13" => "نمین",
			"14" => "نیر",
			"15" => "هیر"
		),
		"IS" => array(
			"0" => "ابریشم",
			"1" => "ابوزیدآباد",
			"2" => "اردستان",
			"3" => "اژیه",
			"4" => "اصفهان",
			"5" => "آبچوئیه",
			"6" => "آران و بیدگل",
			"7" => "بادرود",
			"8" => "برف‌انبار",
			"9" => "بهارستان",
			"10" => "تودشک",
			"11" => "تیران",
			"12" => "جوشقان و کامو",
			"13" => "چمگردان",
			"14" => "خالدآباد",
			"15" => "خمینی‌شهر",
			"16" => "خوانسار",
			"17" => "خور",
			"18" => "خوراسگان",
			"19" => "خورزوق",
			"20" => "داران",
			"21" => "دهق",
			"22" => "دیزیچه",
			"23" => "زاغل",
			"24" => "زرین‌شهر",
			"25" => "زیباشهر",
			"26" => "شاهین‌شهر",
			"27" => "شهرضا",
			"28" => "طالخونچه",
			"29" => "فریدون‌شهر",
			"30" => "کاشان",
			"31" => "کمشجه",
			"32" => "کوهپایه",
			"33" => "کهریزسنگ",
			"34" => "گلپایگان",
			"35" => "گل‌دشت",
			"36" => "گل‌شهر",
			"37" => "گوگد",
			"38" => "مبارکه",
			"39" => "محمد آباد",
			"40" => "میمه",
			"41" => "نجف‌آباد",
			"42" => "نطنز",
			"43" => "نوش‌آباد",
			"44" => "نیاسر",
			"45" => "نیک‌آباد",
			"46" => "ورنامخواست"
		),
		"AL" => array(
			"0" => "اشتهارد",
			"1" => "آسارا",
			"2" => "چهارباغ",
			"3" => "ساوجبلاغ",
			"4" => "طالقان",
			"5" => "کرج",
			"6" => "کمالشهر",
			"7" => "کوهسار",
			"8" => "گرمدره",
			"9" => "ماهدشت",
			"10" => "محمدشهر",
			"11" => "مشکین‌دشت",
			"12" => "نظرآباد",
			"13" => "هشتگرد"
		),
		"IL" => array(
			"0" => "ایلام",
			"1" => "آسمان آباد",
			"2" => "پهله",
			"3" => "دهلران",
			"4" => "موسیان",
			"5" => "مهران",
			"6" => "میمه"
		),
		"AE" => array(
			"0" => "اسکو",
			"1" => "اهر",
			"2" => "ایلخچی",
			"3" => "آبش‌احمد",
			"4" => "آچاچی",
			"5" => "آذرشهر",
			"6" => "آقکند",
			"7" => "باروق",
			"8" => "باسمنج",
			"9" => "بخشایش",
			"10" => "بستان‌آباد",
			"11" => "بناب",
			"12" => "تبریز",
			"13" => "تسوج",
			"14" => "تیکمه‌داش",
			"15" => "جلفا",
			"16" => "چاراویماق",
			"17" => "خامنه",
			"18" => "خراجو",
			"19" => "خسروشهر",
			"20" => "خواجه",
			"21" => "دوزدوزان",
			"22" => "ذوالبین",
			"23" => "زرنق",
			"24" => "زنوز",
			"25" => "سراب",
			"26" => "سردرود",
			"27" => "سهند",
			"28" => "سیس",
			"29" => "شبستر",
			"30" => "شربیان",
			"31" => "شرفخانه",
			"32" => "شندآباد",
			"33" => "صوفیان",
			"34" => "عجب‌شیر",
			"35" => "قره‌آغاج",
			"36" => "کُشکسرای",
			"37" => "کلیبر",
			"38" => "کوزه‌کنان",
			"39" => "گوگان",
			"40" => "لیلان",
			"41" => "مراغه",
			"42" => "مرند",
			"43" => "ملکان",
			"44" => "ممقان",
			"45" => "مهربان",
			"46" => "میانه",
			"47" => "وایقان",
			"48" => "ورزقان",
			"49" => "هادیشهر",
			"50" => "هریس",
			"51" => "هشترود",
			"52" => "هوراند",
			"53" => "یامچی"
		),
		"AW" => array(
			"0" => "ارومیه",
			"1" => "اشنویه",
			"2" => "ایواوغلی",
			"3" => "آواجیق",
			"4" => "باروق",
			"5" => "بازرگان",
			"6" => "بوکان",
			"7" => "پلدشت",
			"8" => "تکاب",
			"9" => "چایپاره",
			"10" => "خوی",
			"11" => "سردشت",
			"12" => "سرو",
			"13" => "سلماس",
			"14" => "سیلوانه",
			"15" => "سیمینه",
			"16" => "سیه‌چشمه",
			"17" => "شاهین‌دژ",
			"18" => "شوط",
			"19" => "فیرورق",
			"20" => "قره ضیاءالدین",
			"21" => "کشاورز",
			"22" => "گردکشانه",
			"23" => "ماکو",
			"24" => "محمدیار",
			"25" => "محمودآباد",
			"26" => "مهاباد",
			"27" => "میاندوآب",
			"28" => "میرآباد",
			"29" => "نالوس",
			"30" => "نقده",
			"31" => "نوشین‌شهر"
		),
		"BU" => array(
			"0" => "امام حسن",
			"1" => "اهرم",
			"2" => "آب‌پخش",
			"3" => "آبدان",
			"4" => "برازجان",
			"5" => "بردخون",
			"6" => "بردستان",
			"7" => "بندر بوشهر",
			"8" => "بندر دیر",
			"9" => "بندر دیلم",
			"10" => "بندر ریگ",
			"11" => "بندر کنگان",
			"12" => "بندر گناوه",
			"13" => "بنک",
			"14" => "تنگ ارم",
			"15" => "جم",
			"16" => "چغادک",
			"17" => "خارک",
			"18" => "خورموج",
			"19" => "دالکی",
			"20" => "دلوار",
			"21" => "ریز",
			"22" => "سعدآباد",
			"23" => "شبانکاره",
			"24" => "شنبه",
			"25" => "طاهری",
			"26" => "عسلویه",
			"27" => "کاکی",
			"28" => "کلمه",
			"29" => "نخل تقی",
			"30" => "وحدتیه"
		),
		"TE" => array(
			"0" => "ارجمند",
			"1" => "اسلام‌شهر",
			"2" => "اندیشه",
			"3" => "آبعلی",
			"4" => "باغستان",
			"5" => "باقرشهر",
			"6" => "پاکدشت",
			"7" => "پردیس (شهر)",
			"8" => "پیشوا",
			"9" => "تهران",
			"10" => "دربندسر",
			"11" => "دماوند",
			"12" => "رباط‌کریم",
			"13" => "رودهن",
			"14" => "ری",
			"15" => "زردبند",
			"16" => "شاهدشهر",
			"17" => "شریف‌آباد",
			"18" => "شمیرانات",
			"19" => "شهریار",
			"20" => "صالح‌آباد",
			"21" => "صباشهر",
			"22" => "فردوسیه",
			"23" => "فشم",
			"24" => "فیروزکوه",
			"25" => "قدس",
			"26" => "قرچک",
			"27" => "کیلان",
			"28" => "گلستان",
			"29" => "لواسان",
			"30" => "ملارد",
			"31" => "نسیم‌شهر",
			"32" => "نصیرشهر",
			"33" => "وحیدیه",
			"34" => "ورامین"
		),
		"CM" => array(
			"0" => "اردل",
			"1" => "آلونی",
			"2" => "بروجن",
			"3" => "بلداجی",
			"4" => "جونقان",
			"5" => "چلگرد",
			"6" => "سامان",
			"7" => "سفیددشت",
			"8" => "سودجان",
			"9" => "سورشجان",
			"10" => "شلمزار",
			"11" => "شهرکرد",
			"12" => "فارسان",
			"13" => "فرادنبه",
			"14" => "فرخ‌شهر",
			"15" => "گندمان",
			"16" => "گهرو",
			"17" => "لردگان",
			"18" => "ناغان",
			"19" => "نافچ",
			"20" => "هفشجان"
		),
		"KJ" => array(
			"0" => "اسفدن",
			"1" => "اسلامیه",
			"2" => "آیَسک",
			"3" => "بشرویه",
			"4" => "بیرجند",
			"5" => "خضری دشت بیاض",
			"6" => "درمیان",
			"7" => "سرایان",
			"8" => "سربیشه",
			"9" => "شوسف",
			"10" => "طارق",
			"11" => "فردوس",
			"12" => "قائن",
			"13" => "قائنات",
			"14" => "نهبندان"
		),
		"KV" => array(
			"0" => "انابد",
			"1" => "باجگیران",
			"2" => "باخرز",
			"3" => "بایگ",
			"4" => "بجستان",
			"5" => "بردسکن",
			"6" => "بیدخت",
			"7" => "تایباد",
			"8" => "تربت جام",
			"9" => "تربت حیدریه",
			"10" => "چاپشلو",
			"11" => "چکنه",
			"12" => "چناران",
			"13" => "خرو",
			"14" => "خلیل‌آباد",
			"15" => "داورزن",
			"16" => "دررود",
			"17" => "دولت‌آباد",
			"18" => "رودآب",
			"19" => "سبزوار",
			"20" => "سرخس",
			"21" => "سلامی",
			"22" => "شادمهر",
			"23" => "شاندیز",
			"24" => "طرقبه",
			"25" => "عشق آباد",
			"26" => "فرهادگرد",
			"27" => "فریمان",
			"28" => "فیروزه",
			"29" => "فیض‌آباد",
			"30" => "قاسم‌آباد",
			"31" => "قدمگاه",
			"32" => "قوچان",
			"33" => "کاخک",
			"34" => "کاریز",
			"35" => "کاشمر",
			"36" => "کلات",
			"37" => "گناباد",
			"38" => "مشهد مقدس",
			"39" => "نصرآباد",
			"40" => "نوخندان",
			"41" => "نیشابور",
			"42" => "نیل‌شهر",
			"43" => "همت آباد"
		),
		"KS" => array(
			"0" => "اسفراین",
			"1" => "آشخانه",
			"2" => "بجنورد",
			"3" => "پیش‌قلعه",
			"4" => "حصار گرم‌خان",
			"5" => "درق",
			"6" => "راز",
			"7" => "سنخواست",
			"8" => "شیروان",
			"9" => "صفی‌آباد",
			"10" => "لوجلی"
		),
		"KZ" => array(
			"0" => "اروندکنار",
			"1" => "امیدیه",
			"2" => "اندیمشک",
			"3" => "اهواز",
			"4" => "ایذه",
			"5" => "آبادان",
			"6" => "باغ‌ملک",
			"7" => "بندر امام خمینی ره",
			"8" => "بهبهان",
			"9" => "چمران",
			"10" => "حر ریاحی",
			"11" => "خرمشهر",
			"12" => "دزفول",
			"13" => "دشت آزادگان",
			"14" => "رامهرمز",
			"15" => "سوسنگرد",
			"16" => "شادگان",
			"17" => "شوش",
			"18" => "شوشتر",
			"19" => "صیدون",
			"20" => "گتوند",
			"21" => "لالی",
			"22" => "ماهشهر",
			"23" => "مسجد سلیمان",
			"24" => "میانرود",
			"25" => "مینوشهر",
			"26" => "هفتگل",
			"27" => "هندیجان"
		),
		"ZA" => array(
			"0" => "ارمغان‌خانه",
			"1" => "آب‌بر",
			"2" => "خرمدره",
			"3" => "زنجان",
			"4" => "سجاس",
			"5" => "سهرورد",
			"6" => "قیدار",
			"7" => "ماه‌نشان"
		),
		"SM" => array(
			"0" => "ایوانکی",
			"1" => "آرادان",
			"2" => "بسطام",
			"3" => "دامغان",
			"4" => "درجزین",
			"5" => "سرخه",
			"6" => "سمنان",
			"7" => "شاهرود",
			"8" => "شهمیرزاد",
			"9" => "کلاته خیج",
			"10" => "گرمسار",
			"11" => "مجن",
			"12" => "مهدی‌شهر"
		),
		"SB" => array(
			"0" => "ادیمی",
			"1" => "اسپکه",
			"2" => "ایرانشهر",
			"3" => "بزمان",
			"4" => "بمپور",
			"5" => "بنت",
			"6" => "بنجار",
			"7" => "پیشین",
			"8" => "جالق",
			"9" => "چابهار",
			"10" => "خاش",
			"11" => "دوست‌محمد",
			"12" => "راسک",
			"13" => "زابل",
			"14" => "زابلی",
			"15" => "زاهدان",
			"16" => "زهک",
			"17" => "سراوان",
			"18" => "سرباز",
			"19" => "سوران",
			"20" => "فنوج",
			"21" => "قصرقند",
			"22" => "کنارک",
			"23" => "گلمورتی",
			"24" => "محمدآباد",
			"25" => "میرجاوه",
			"26" => "نصرت‌آباد",
			"27" => "نگور",
			"28" => "نوک‌آباد",
			"29" => "نیک‌شهر"
		),
		"FA" => array(
			"0" => "اَهِل",
			"1" => "اِوَز",
			"2" => "اردکان",
			"3" => "ارسنجان",
			"4" => "استهبان",
			"5" => "اشکنان",
			"6" => "اقلید",
			"7" => "ایزدخواست",
			"8" => "آباده",
			"9" => "باب انار",
			"10" => "بالاده",
			"11" => "بنارویه",
			"12" => "بهمن",
			"13" => "بیرم",
			"14" => "جنت‌شهر",
			"15" => "جهرم",
			"16" => "خاوران",
			"17" => "خرامه",
			"18" => "خشت",
			"19" => "خنج",
			"20" => "خور",
			"21" => "خومه‌زار",
			"22" => "رستم",
			"23" => "سلطان شهر",
			"24" => "سورمق",
			"25" => "سوریان",
			"26" => "ششده",
			"27" => "شهر خنج",
			"28" => "شیراز",
			"29" => "صغاد",
			"30" => "صفاشهر",
			"31" => "علامرودشت",
			"32" => "فسا",
			"33" => "فیروزآباد",
			"34" => "قائمیه",
			"35" => "قطب‌آباد",
			"36" => "قیر",
			"37" => "کَوار",
			"38" => "کازرون",
			"39" => "کامفیروز",
			"40" => "کنارتخته",
			"41" => "گراش",
			"42" => "گله‌دار",
			"43" => "لار",
			"44" => "لامرد",
			"45" => "مُهر",
			"46" => "مرکزی گراش",
			"47" => "مرودشت",
			"48" => "میمند",
			"49" => "نودان",
			"50" => "نورآباد",
			"51" => "نورآباد ممسنی",
			"52" => "نی‌ریز",
			"53" => "وراوی"
		),
		"QZ" => array(
			"0" => "ارداق",
			"1" => "اسفرورین",
			"2" => "الوند",
			"3" => "بوئین‌زهرا",
			"4" => "تاکستان",
			"5" => "خاکعلی",
			"6" => "شال",
			"7" => "قزوین",
			"8" => "کوهین",
			"9" => "محمودآباد نمونه",
			"10" => "نرجه"
		),
		"QM" => array(
			"0" => "دستجرد",
			"1" => "قم",
			"2" => "قنوات",
			"3" => "جعفریه",
			"4" => "کهک",
			"5" => "دستجرد",
			"6" => "سلفچگان",
		),
		"KD" => array(
			"0" => "بابارشانی",
			"1" => "بانه",
			"2" => "بیجار",
			"3" => "چناره",
			"4" => "دهگلان",
			"5" => "زرینه",
			"6" => "سریش آباد",
			"7" => "سقز",
			"8" => "سنندج",
			"9" => "شویشه",
			"10" => "صاحب",
			"11" => "قروه",
			"12" => "مریوان"
		),
		"KE" => array(
			"0" => "اختیارآباد",
			"1" => "امین‌شهر",
			"2" => "اندوهجرد",
			"3" => "باغین",
			"4" => "بافت",
			"5" => "بردسیر",
			"6" => "بروات",
			"7" => "بزنجان",
			"8" => "بم",
			"9" => "بهرمان",
			"10" => "پاریز",
			"11" => "جبالبارز",
			"12" => "جوپار",
			"13" => "جیرفت",
			"14" => "چترود",
			"15" => "دهج",
			"16" => "رابر",
			"17" => "راور",
			"18" => "راین",
			"19" => "رفسنجان",
			"20" => "ریحان‌شهر",
			"21" => "زنگی‌آباد",
			"22" => "زیدآباد",
			"23" => "سیرجان",
			"24" => "شهداد",
			"25" => "صفائیه",
			"26" => "فهرج",
			"27" => "کاظم‌آباد",
			"28" => "کرمان",
			"29" => "کوهبنان",
			"30" => "گلزار",
			"31" => "محی‌آباد",
			"32" => "مردهک",
			"33" => "منوجان",
			"34" => "نرماشیر",
			"35" => "نظام‌شهر",
			"36" => "نودژ",
			"37" => "یزدان‌شهر"
		),
		"BK" => array(
			"0" => "ازگله",
			"1" => "اسلام‌آباد غرب",
			"2" => "باینگان",
			"3" => "بیستون",
			"4" => "پاوه",
			"5" => "جوانرود",
			"6" => "روانسر",
			"7" => "سرپل ذهاب",
			"8" => "سرمست",
			"9" => "سطر",
			"10" => "سنقر",
			"11" => "سومار",
			"12" => "صحنه",
			"13" => "قصر شیرین",
			"14" => "کرمانشاه",
			"15" => "کنگاور",
			"16" => "کوزران",
			"17" => "گهواره",
			"18" => "گیلانغرب",
			"19" => "میان‌راهان",
			"20" => "نوسود",
			"21" => "هرسین",
			"22" => "هلشی"
		),
		"KB" => array(
			"0" => "پاتاوه",
			"1" => "چرام",
			"2" => "چیتاب",
			"3" => "دوگنبدان",
			"4" => "دهدشت",
			"5" => "دیشموک",
			"6" => "سوق",
			"7" => "سی سخت",
			"8" => "کفشکنان",
			"9" => "گراب سفلی",
			"10" => "لنده",
			"11" => "لیکک",
			"12" => "محله چینی‌ها",
			"13" => "یاسوج"
		),
		"GO" => array(
			"0" => "انبار آلوم",
			"1" => "اینچه‌بُرون",
			"2" => "آزادشهر",
			"3" => "آق قلا",
			"4" => "بندر ترکمن",
			"5" => "بندر گز",
			"6" => "خان‌ببین",
			"7" => "دلند",
			"8" => "سرخنکلاته",
			"9" => "سیمین‌شهر",
			"10" => "علی‌آباد کتول",
			"11" => "کردکوی",
			"12" => "کلاله",
			"13" => "گالیکش",
			"14" => "گرگان",
			"15" => "گلستان",
			"16" => "گمیشان",
			"17" => "گنبدکاووس",
			"18" => "مینودشت",
			"19" => "نگین‌شهر",
			"20" => "نوکنده"
		),
		"GI" => array(
			"0" => "احمدسرگوراب",
			"1" => "اسالم",
			"2" => "اطاقور",
			"3" => "املش",
			"4" => "آستانه اشرفیه",
			"5" => "بازارجمعه",
			"6" => "بندر انزلی",
			"7" => "بندر آستارا",
			"8" => "تالش",
			"9" => "توتکابن",
			"10" => "جیرنده",
			"11" => "چابکسر",
			"12" => "چوبر",
			"13" => "خلیفه محله",
			"14" => "رشت",
			"15" => "رضوان‌شهر",
			"16" => "رودبار",
			"17" => "رودبار زیتون",
			"18" => "رودبنه",
			"19" => "شفت",
			"20" => "شلمان",
			"21" => "صومعه سرا",
			"22" => "فومن",
			"23" => "کوچصفهان",
			"24" => "لاهیجان",
			"25" => "لشت نشا",
			"26" => "لنگرود",
			"27" => "لوشان",
			"28" => "لوندویل",
			"29" => "لیسار",
			"30" => "مارلیک",
			"31" => "ماسال",
			"32" => "مرجغل",
			"33" => "منجیل",
			"34" => "واجارگاه",
			"35" => "هشتپر"
		),
		"LO" => array(
			"0" => "ازنا",
			"1" => "الشتر",
			"2" => "الیگودرز",
			"3" => "بروجرد",
			"4" => "پل‌دختر",
			"5" => "چالانچولان",
			"6" => "خرم‌آباد",
			"7" => "درب گنبد",
			"8" => "دورود",
			"9" => "زاغه",
			"10" => "سراب‌دوره",
			"11" => "فرزیان",
			"12" => "فیروزآباد",
			"13" => "کونانی",
			"14" => "کوهدشت",
			"15" => "گراب",
			"16" => "معمولان",
			"17" => "نورآباد",
			"18" => "ویسیان"
		),
		"MN" => array(
			"0" => "امیرشهر",
			"1" => "ایزدشهر",
			"2" => "آلاشت",
			"3" => "آمل",
			"4" => "بابل",
			"5" => "بابلسر",
			"6" => "بلده",
			"7" => "بهشهر",
			"8" => "پل سفید",
			"9" => "تنکابن",
			"10" => "جویبار",
			"11" => "چالوس",
			"12" => "چمستان",
			"13" => "خلیل‌شهر",
			"14" => "خوش‌رودپی",
			"15" => "دابودشت",
			"16" => "رامسر",
			"17" => "رستمکلا",
			"18" => "رویان",
			"19" => "زرگرمحله",
			"20" => "ساری",
			"21" => "شیرگاه",
			"22" => "فریدون‌کنار",
			"23" => "فریم",
			"24" => "قائم‌شهر",
			"25" => "کلارآباد",
			"26" => "کلاردشت",
			"27" => "کله‌بست",
			"28" => "کوهی‌خیل",
			"29" => "کیاسر",
			"30" => "گتاب",
			"31" => "گرجی‌محله",
			"32" => "گزنک",
			"33" => "گلوگاه",
			"34" => "مرزن‌آباد",
			"35" => "مرزیکلا",
			"36" => "نشتارود",
			"37" => "نوشهر"
		),
		"MK" => array(
			"0" => "اراک",
			"1" => "انجدان",
			"2" => "آشتیان",
			"3" => "پرندک",
			"4" => "تفرش",
			"5" => "توره",
			"6" => "خمین",
			"7" => "خنداب",
			"8" => "رازقان",
			"9" => "زاویه",
			"10" => "ساوه",
			"11" => "سنجان",
			"12" => "شازند",
			"13" => "ضامنجان",
			"14" => "غرق‌آباد",
			"15" => "فرمهین",
			"16" => "فیجان",
			"17" => "قورچی‌باشی",
			"18" => "کرهرود",
			"19" => "کمیجان",
			"20" => "مأمونیه",
			"21" => "محلات",
			"22" => "نراق",
			"23" => "نوبران",
			"24" => "نیم‌ور"
		),
		"HG" => array(
			"0" => "ابوموسی",
			"1" => "بستک",
			"2" => "بندر چارک",
			"3" => "بندر خمیر",
			"4" => "بندر عباس",
			"5" => "بندر لنگه",
			"6" => "بندرخمیر",
			"7" => "پارسیان",
			"8" => "جاسک",
			"9" => "جزیره کیش",
			"10" => "جناح",
			"11" => "حاجی‌آباد",
			"12" => "درگهان",
			"13" => "رودان",
			"14" => "سندرک",
			"15" => "سیریک",
			"16" => "شهر رویدر",
			"17" => "شهر کنگ",
			"18" => "شهر کیش",
			"19" => "فین",
			"20" => "قشم",
			"21" => "لنگه",
			"22" => "میناب"
		),
		"HD" => array(
			"0" => "برزول",
			"1" => "بهار",
			"2" => "حسین آباد ناظم",
			"3" => "دانشگاه پیام نور لالجین",
			"4" => "رزن",
			"5" => "زنگنه",
			"6" => "سامن",
			"7" => "صالح‌آباد",
			"8" => "فامنین",
			"9" => "فرسفج",
			"10" => "فیروزان",
			"11" => "قهاوند",
			"12" => "کبود رآهنگ",
			"13" => "گیان",
			"14" => "لالجین",
			"15" => "ملایر",
			"16" => "مهاجران",
			"17" => "نهاوند",
			"18" => "ویژه ملایر",
			"19" => "همدان"
		),
		"YA" => array(
			"0" => "اردکان",
			"1" => "بافق",
			"2" => "دیهوک",
			"3" => "فردوس",
			"4" => "مروست",
			"5" => "مهردشت",
			"6" => "میبد",
			"7" => "ندوشن",
			"8" => "یزد"
		)
	);
	
	return isset($states_city[$state]) ? $states_city[$state] : array();
}
