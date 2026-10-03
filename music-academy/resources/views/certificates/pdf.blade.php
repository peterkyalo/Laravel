<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate — {{ $certificate->code }}</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background-color: #fafafa;
            color: #1a1a1a;
        }
        .outer-border {
            border: 10px solid #1a237e;
            padding: 6px;
            height: 94%;
            box-sizing: border-box;
        }
        .inner-border {
            border: 2px solid #d4af37;
            padding: 30px 40px;
            text-align: center;
            height: 89%;
            box-sizing: border-box;
            background-color: #ffffff;
        }
        .header {
            font-size: 14px;
            letter-spacing: 4px;
            color: #555555;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .sub-header {
            font-size: 10px;
            letter-spacing: 2px;
            color: #888888;
            margin-bottom: 25px;
        }
        .title {
            font-size: 38px;
            color: #1a237e;
            margin: 10px 0 15px 0;
            font-weight: bold;
        }
        .certifies {
            font-size: 15px;
            font-style: italic;
            color: #666666;
            margin-bottom: 15px;
        }
        .student-name {
            font-size: 32px;
            color: #0b0f19;
            font-weight: bold;
            border-bottom: 2px solid #d4af37;
            display: inline-block;
            padding-bottom: 6px;
            margin-bottom: 20px;
        }
        .reason {
            font-size: 13px;
            color: #444444;
            max-width: 600px;
            margin: 0 auto 15px auto;
            line-height: 1.6;
        }
        .course-title {
            font-size: 22px;
            color: #b45309;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .footer-table {
            width: 100%;
            margin-top: 20px;
        }
        .sign-cell {
            width: 35%;
            text-align: center;
            vertical-align: bottom;
        }
        .seal-cell {
            width: 30%;
            text-align: center;
            vertical-align: middle;
        }
        .line {
            border-top: 1px solid #333333;
            margin: 0 20px 5px 20px;
        }
        .label {
            font-size: 10px;
            color: #777777;
            text-transform: uppercase;
        }
        .seal-circle {
            display: inline-block;
            border: 2px solid #d4af37;
            border-radius: 50%;
            width: 65px;
            height: 65px;
            line-height: 65px;
            font-size: 10px;
            font-weight: bold;
            color: #d4af37;
        }
        .meta {
            margin-top: 20px;
            font-size: 9px;
            color: #888888;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="outer-border">
        <div class="inner-border">
            <div class="header">Harmonia Music Academy</div>
            <div class="sub-header">Conservatory of Classical &amp; Contemporary Music</div>

            <div class="title">Certificate of Mastery</div>
            <div class="certifies">This is to officially certify that</div>

            <div class="student-name">{{ $certificate->enrollment->user->name }}</div>

            <div class="reason">
                has successfully completed all required masterclass lessons, practice recording submissions, and harmonic theory examinations for
            </div>

            <div class="course-title">{{ $certificate->enrollment->course->title }}</div>

            <table class="footer-table">
                <tr>
                    <td class="sign-cell">
                        <div style="font-weight: bold; font-size: 13px; margin-bottom: 4px;">{{ $certificate->enrollment->course->instructor->name }}</div>
                        <div class="line"></div>
                        <div class="label">Course Instructor</div>
                    </td>
                    <td class="seal-cell">
                        <div class="seal-circle">VERIFIED</div>
                    </td>
                    <td class="sign-cell">
                        <div style="font-weight: bold; font-size: 13px; margin-bottom: 4px;">{{ $certificate->issued_at->format('F d, Y') }}</div>
                        <div class="line"></div>
                        <div class="label">Date of Conferral</div>
                    </td>
                </tr>
            </table>

            <div class="meta">
                Credential ID: {{ $certificate->code }} &bull; Harmonia Music Academy Registry
            </div>
        </div>
    </div>
</body>
</html>
