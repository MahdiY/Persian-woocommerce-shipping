=== افزونه حمل و نقل ووکامرس ===
Contributors: mahdiy
Tags: woocommerce,shipping,persian woocommerce,persian
Donate link: http://donate.woocommerce.ir/persian-woocommerce-shipping/
Requires at least: 5.0
Tested up to: 5.4
Requires PHP: 7.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ارسال مرسوله های ووکامرس از طریق پست پیشتاز، سفارشی، پیک موتوری و تیپاکس با محاسبه خودکار تعرفه

== Description ==
با استفاده از این افزونه می توانید با روش های ارسال پستی متنوع، محصولات فیزیکی ووکامرس خود را به فروش بگذارید.

= امکانات =
* دارای چهار روش ارسال: پیشتاز، سفارشی، تیپاکس و پیک موتوری
* قابلیت اتصال به پیشخوان مجازی پست
* ابزارهای کاربردی برای مدیریت حمل و نقل
* قابلیت افزودن بی نهایت استان، شهر و محله
* قابلیت شخصی سازی کامل قیمت ها برای هر آدرس
* قابلیت افزودن هزینه ثابت یا درصدی به قیمت نهایی
* محاسبه اتوماتیک هزینه پست پیشتاز و سفارشی بر اساس وزن (تعرفه سال 1398)
* سازگار با چهار واحد پولی ایران در ووکامرس
* سازگار با تمامی افزونه های فاکتور و برچسب ساز
* و ...

= سازگاری =
* Woocommerce 3.9.*, 4.*
* Wordpress 5.*
* PHP 7.*

= مستندات =
شما می توانید مستندات فنی این افزونه را از [اینجا](https://mahdiy.gitbook.io/persian-woocommerce-shipping) مشاهده کنید.

== Installation ==
= نصب =
1. فایل های افزونه را در مسیر `/wp-content/plugins/persian-woocommerce-shipping` آپلود کنید، یا از صفحه افزونه های وردپرس افزونه را مستقیم نصب کنید
1. افزونه را از منو افزونه ها فعال کنید
1. منطقه حمل و نقل خود را از پیکربندی ووکامرس ایجاد کنید و متد های حمل و نقل مورد نظر را اضافه کنید
1. با قدرت به فروش خود آماده دهید!

= پیکربندی =
* در صورتی که از افزونه ووکامرس فارسی استفاده می کنید، از منوی ووکامرس فارسی > ابزار ها گزینه فعالسازی شهر ها را غیرفعال کنید
* برای عملکرد بهتر افزونه گزینه "فعال کردن محاسبه‌گر هزینه ارسال در برگه سبدخرید" را غیرفعال کنید
* توصیه می شود گزینه "هزینه حمل كالا را تا زمانی كه خریدار آدرس خود را وارد نكرده است نمایش نده" را فعال کنید

= Documents =

Read more document in [Persian Woocommerce Shipping Documents](https://mahdiy.gitbook.io/persian-woocommerce-shipping).

== Frequently Asked Questions ==
Ask your questions in [Persian Woocommerce Shipping](http://mahdiy.ir/plugins/persian-woocommerce-shipping).

== Screenshots ==

1. Add shipping method to your shipping zone.
2. Enable shipping method and configuration.
3. List of States, cities, districts.
4. Bulk editing prices.
5. Custom settings for every state, city or district.

== Changelog ==
= 2.0.5 =
* Fix: Apply free shipping in methods
* Improve: Add rate function
= 2.0.4 =
* Fix: Compatibility with php 7.0
* Improve: File names & check constants before define
= 2.0.3 =
* Fix: Fix tapin method settings
* Fix: Courier method destination for tapin
* Fix: Posteketab credit
= 2.0.2 =
* Fix: Edit shipping methods title
* Tweak: Add filter for default state, city, district
= 2.0.1 =
* Fix: Check PW function is exists (persian woocommerce is active) to enable tools
= 2.0.0 =
* Tweak: Add tapin (Virtual post panel)
* Tweak: Add IR post standard cities list
* Tweak: Add tools page and three practical tools for store management
* Tweak: Save selected city for no logged in users
* Tweak: New Courier method + select city to show method
* Tweak: Add cities list to edit address in admin panel
* Tweak: Compatibility with woocommerce 4.0.0
* Improve: Courier & Tipax calculation algorithm
* Improve: Cache states and cities list to increase speed in checkout page
* Improve: Decrease number of requests in checkout page
* Fix: Select default city on load cities list
* Fix: Some reported bugs in persian script forums
= 1.2.3 =
* Tweak: Updated Post Prices 1398 (new)
= 1.2.2 =
* Tweak: Updated Post Prices 1398
= 1.2.1 =
* Tweak: Compatibility with woocommerce 3.7.0
= 1.2 =
* Fix: Default city and district field priority
* Tweak: Compatibility with checkout manager plguins
* Tweak: Compatibility with Wordpress 5.2.2
= 1.1.2 =
* Fix: validation of state & city IDs for empty data
* Tweak: Compatibility with Wordpress 5.1
= 1.1.1 =
* Fix: validation of state & city IDs for virtual products
* Tweak: Compatibility with woocommerce 3.5.2
= 1.1 =
* Fix: Save name of state & city instead of IDs
* Fix: validation of state & city IDs
* Tweak: Add pws_city_class & pws_district_class filters
* Tweak: Add pws_city_priority & pws_district_priority filters
* Tweak: Compatibility with all invoice and ticket generators
* Tweak: Lunch documents on https://mahdiy.gitbook.io/persian-woocommerce-shipping
= 1.0 =
* Tweak: Improve speed of loading state & city
* Tweak: Compatibility with woocommerce 3.4.5
= 0.9.1 =
* Fix: save Country / State setting
* Tweak: Compatibility with woocommerce 3.2.2
= 0.9 =
* Tweak: Compatibility with woocommerce 3.2.1
* Tweak: Add action for cities
= 0.8.8 =
* Tweak: Compatibility with woocommerce 3.0.7
* Tweak: Add filter for rates
= 0.8.6 =
* Fix: Disable method
* Tweak: Postal rates updated
* Tweak: improve selecting city and district in checkout page
* Tweak: Compatibility with persian woocommerce sms
= 0.8.2 =
* Fix: Show method image
* Tweak: Compatibility with persian woocommerce states
= 0.8.1 =
* Fix: Apply weight on the price
= 0.8 =
* Start, First version of plugin

== Upgrade Notice ==
= 2.0.0 =
* این نسخه دارای تغییرات بنیادی است. لطفا قبل از بروزرسانی از پایگاه داده خود بکاپ تهیه کنید.
= 1.2.1 =
* سازگاری با ووکامرس 3.7.0
= 0.9.1 =
* مشکل ذخیره کشور و استان آدرس فروشگاه حل شد
= 0.8.6 =
* در صورتی که پس از بروزرسانی افزونه، نرخ بیمه روش های پستی از 5000 ریال به 8000 ریال بروزرسانی نشد، اینکار را بصورت دستی انجام دهید
