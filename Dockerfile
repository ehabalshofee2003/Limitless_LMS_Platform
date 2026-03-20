# 1. نقطة البداية (Base Image)
# نستخدم صورة PHP الرسمية مع Nginx (أو Apache) أو FPM
# سنستخدم صورة جاهزة تحتوي PHP 8.2 و Composer
FROM php:8.2-fpm

# 2. تعيين مجلد العمل
WORKDIR /var/www

# 3. تثبيت الاعتمادات النظامية (System Dependencies)
# هذه المكتبات ضرورية لعمل Laravel (مثل معالجة الصور، zip، إلخ)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor

# 4. تثبيت إضافات PHP (PHP Extensions)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 5. تثبيت Composer
# نسخ composer من الصورة الرسمية إلى صورتنا
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. نسخ ملفات المشروع
# ننسخ كل شيء من المجلد الحالي (مشروعك) إلى مجلد العمل داخل الحاوية
COPY . /var/www

# 7. إعداد الصلاحيات (Permissions)
# Laravel يحتاج صلاحيات خاصة للكتابة في مجلدات التخزين والكاش
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/bootstrap/cache

# 8. تشغيل أوامر التثبيت (اختياري عند البناء)
RUN composer install --no-dev --optimize-autoloader

# 9. تشغيل السيرفر
# هنا نستخدم أمراً لتشغيل PHP-FPM الذي يستمع على المنفذ 9000
CMD ["php-fpm"]

# فتح المنفذ
EXPOSE 9000