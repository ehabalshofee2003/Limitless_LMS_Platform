<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Limitless Certificate</title>
    <style>
        /* إعدادات الخط والصفحة */
        @page { margin: 0mm; size: A4 landscape; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* الحاوية الرئيسية للشهادة (مع إطار ذهبي) */
        .certificate-wrapper {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            border: 20px solid transparent;
            border-image: linear-gradient(45deg, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
            border-image-slice: 1;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* إطار داخلي زخرفي */
        .inner-border {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 2px solid #d4af37; /* ذهبي */
            pointer-events: none;
        }

        /* زخرفة في الزوايا */
        .corner {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid #d4af37;
            border-radius: 50%;
            opacity: 0.5;
        }
        .top-left { top: 30px; left: 30px; border-right: none; border-bottom: none; border-radius: 0; }
        .top-right { top: 30px; right: 30px; border-left: none; border-bottom: none; border-radius: 0; }
        .bottom-left { bottom: 30px; left: 30px; border-right: none; border-top: none; border-radius: 0; }
        .bottom-right { bottom: 30px; right: 30px; border-left: none; border-top: none; border-radius: 0; }

        /* المحتوى */
        .content {
            text-align: center;
            padding: 60px 80px;
            z-index: 10;
            position: relative;
        }

        /* تنسيق الشعار */
        .logo-section {
            margin-bottom: 20px;
        }
        .logo-icon {
            width: 60px;
            height: 60px;
            fill: #b38728;
        }
        .brand-name {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #2c3e50;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* العنوان الرئيسي */
        .main-title {
            font-size: 45px;
            font-weight: 300;
            color: #2c3e50;
            letter-spacing: 5px;
            margin: 20px 0;
            font-family: serif;
            border-bottom: 2px solid #d4af37;
            display: inline-block;
            padding-bottom: 10px;
        }

        /* نص الشهادة */
        .cert-text {
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
        }

        .student-name {
            font-size: 40px;
            font-family: cursive, sans-serif;
            color: #b38728; /* ذهبي */
            margin: 15px 0 30px 0;
            font-weight: bold;
        }

        .course-name {
            font-size: 22px;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 30px;
        }

        /* التوقيع والتاريخ */
        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding: 0 40px;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 100%;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 12px;
            color: #777;
            font-weight: bold;
        }

        /* ختم الشهادة (ID) */
        .cert-id {
            position: absolute;
            bottom: 30px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="certificate-wrapper">
    <!-- الإطار الداخلي -->
    <div class="inner-border"></div>
    
    <!-- الزخارف -->
    <div class="corner top-left"></div>
    <div class="corner top-right"></div>
    <div class="corner bottom-left"></div>
    <div class="corner bottom-right"></div>

    <div class="content">
        
        <!-- الشعار (SVG مدمج) -->
        <div class="logo-section">
            <!-- هذا كود SVG لشعار "Limitless" يمثل سهم لا ينتهي -->
            <svg class="logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L1 12l4 4 7-7 7 7 4-4L12 2zm0 5.5L6.5 12l5.5 5.5 5.5-5.5L12 7.5z"/>
            </svg>
            <div class="brand-name">Limitless</div>
        </div>

        <div class="main-title">Certificate of Completion</div>

        <p class="cert-text">This is to certify that</p>
        
        <div class="student-name">{{ $student_name }}</div>
        
        <p class="cert-text">Has successfully completed the course</p>
        
        <div class="course-name">{{ $course_name }}</div>

        <!-- قسم التوقيع والتاريخ -->
        <div class="footer-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Date Issued</div>
                <div style="font-size: 14px;">{{ $date }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Instructor</div>
                <div style="font-size: 14px;">{{ $instructor_name }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-title">Director</div>
                <div style="font-size: 14px;">Dr. Ehab Khan</div>
            </div>
        </div>

        <!-- رقم الشهادة -->
        <div class="cert-id">
            Certificate ID: LMT-{{ $cohort_id }}-{{ $student_name }}
        </div>

    </div>
</div>

</body>
</html>