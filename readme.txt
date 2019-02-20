=== افزونه حمل و نقل ووکامرس ===
Contributors: mahdiy
Tags: woocommerce,shipping,persian woocommerce,persian
Donate link: http://donate.woocommerce.ir/persian-woocommerce-shipping/
Requires at least: 4.6
Tested up to: 5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ارسال مرسوله های ووکامرس از طریق پست پیشتاز، سفارشی، پیک موتوری و تیپاکس با محاسبه خودکار تعرفه

== Description ==
با استفاده از این افزونه می توانید با روش های ارسال پستی متنوع، محصولات فیزیکی ووکامرس خود را به فروش بگذارید.

= امکانات =
* دارای چهار روش ارسال: پیشتاز، سفارشی، تیپاکس و پیک موتوری
* قابلیت افزودن بی نهایت استان، شهر و محله
* قابلیت شخصی سازی کامل قیمت ها برای هر آدرس
* قابلیت افزودن هزینه ثابت یا درصدی به قیمت نهایی
* محاسبه اتوماتیک هزینه پست پیشتاز و سفارشی بر اساس وزن
* سازگار با چهار واحد پولی ایران در ووکامرس
* سازگار با تمامی افزونه های فاکتور و برچسب ساز
* و ...

= سازگاری =
* Woocommece 3.5.*
* Wordpress 4.* & 5.*
* PHP 5.6.* & 7.*

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
Ask your questions in [Persian Woocommerce Shipping](http://sabira.ir/plugins/persian-woocommerce-shipping).

== Screenshots ==

1. Add shipping method to your shipping zone.
2. Enable shipping method and configuration.
3. List of States, cities, districts.
4. Bulk editing prices.
5. Custom settings for every state, city or district.

== Changelog ==
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
= 0.9.1 =
* مشکل ذخیره کشور و استان آدرس فروشگاه حل شد
= 0.8.6 =
* در صورتی که پس از بروزرسانی افزونه، نرخ بیمه روش های پستی از 5000 ریال به 6500 ریال بروزرسانی نشد، اینکار را بصورت دستی انجام دهید
