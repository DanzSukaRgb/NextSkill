<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', serif;
            background: #f5f5f5;
        }

        .certificate-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .certificate {
            width: 900px;
            height: 650px;
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 3px solid #d4af37;
            position: relative;
            overflow: hidden;
        }

        /* Decorative corners */
        .certificate::before,
        .certificate::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 50px;
            border: 2px solid #d4af37;
        }

        .certificate::before {
            top: 15px;
            left: 15px;
            border-right: none;
            border-bottom: none;
        }

        .certificate::after {
            top: 15px;
            right: 15px;
            border-left: none;
            border-bottom: none;
        }

        .corner-bottom {
            position: absolute;
            width: 50px;
            height: 50px;
            border: 2px solid #d4af37;
        }

        .corner-bottom-left {
            bottom: 15px;
            left: 15px;
            border-right: none;
            border-top: none;
        }

        .corner-bottom-right {
            bottom: 15px;
            right: 15px;
            border-left: none;
            border-top: none;
        }

        /* Content area */
        .content {
            padding: 60px;
            text-align: center;
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header section */
        .header {
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            font-style: italic;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 30px 0;
        }

        .awarded-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }

        .student-name {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            text-decoration: underline;
            text-decoration-color: #d4af37;
            text-decoration-thickness: 2px;
            text-underline-offset: 8px;
        }

        .achievement-text {
            font-size: 14px;
            color: #666;
            margin: 20px 0;
            line-height: 1.6;
        }

        .course-title {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin: 15px 0;
        }

        /* Footer section */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 40px;
            gap: 30px;
        }

        .signature-block {
            flex: 1;
            text-align: center;
            border-top: 2px solid #333;
            padding-top: 15px;
        }

        .signature-name {
            font-size: 12px;
            color: #333;
            margin-top: 5px;
        }

        .certificate-number {
            flex: 1;
            text-align: center;
        }

        .cert-number-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .cert-number {
            font-size: 12px;
            color: #333;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .date-block {
            flex: 1;
            text-align: center;
        }

        .date-label {
            font-size: 10px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .date {
            font-size: 12px;
            color: #333;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(100, 100, 100, 0.05);
            z-index: 1;
            pointer-events: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate">
            <!-- Watermark -->
            <div class="watermark">CERTIFICATE</div>

            <!-- Decorative corners -->
            <div class="corner-bottom corner-bottom-left"></div>
            <div class="corner-bottom corner-bottom-right"></div>

            <!-- Content -->
            <div class="content">
                <!-- Header -->
                <div class="header">
                    <div class="logo">NextSkill</div>
                    <div class="certificate-title">Certificate of Achievement</div>
                    <div class="subtitle">Professional Learning Platform</div>
                </div>

                <!-- Main Content -->
                <div class="main-content">
                    <div class="awarded-text">This is to certify that</div>
                    
                    <div class="student-name">{{ $student_name }}</div>
                    
                    <div class="achievement-text">
                        has successfully completed the course
                    </div>

                    <div class="course-title">{{ $course_title }}</div>

                    <div class="achievement-text">
                        and demonstrated mastery of the course materials and objectives.
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="date-block">
                        <div class="date-label">Date Issued</div>
                        <div class="date">{{ $issued_at }}</div>
                    </div>

                    <div class="certificate-number">
                        <div class="cert-number-label">Certificate Number</div>
                        <div class="cert-number">{{ $certificate_number }}</div>
                    </div>

                    <div class="signature-block">
                        <div class="signature-name">Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
