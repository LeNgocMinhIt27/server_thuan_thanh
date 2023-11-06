<!DOCTYPE html>
<html lang="vi">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DANH SÁCH SINH VIÊN ĐIỂM DANH</title>
    <style>
        @font-face {
            font-family: 'TimeNewRoman';
            font-style: normal;
            src: url("../storage/fonts/SVN-Times-New-Roman.ttf") format('truetype');
        }

        @font-face {
            font-family: 'TimeNewRoman';
            font-style: bold;
            font-weight: 700;
            src: url("../storage/fonts/SVN-Times-New-Roman-Bold.ttf") format('truetype');
        }

        .break_page {
            page-break-after: always;
        }

        .main_title {
            text-align: center;

        }

        #customers {
            padding-top: 16px;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            color: black;
        }

        * {
            font-family: 'TimeNewRoman';
        }
    </style>
</head>

<body>
    <h2 class="main_title">DANH S&Aacute;CH SINH VI&Ecirc;N</h2>
    <table style="width:499.5pt;margin-left:-9.0pt;border-collapse:collapse;border:none;">
        <tbody>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">Học phần:
                            {{ $subData['ten_hp'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">M&atilde; học phần:
                            {{ $subData['ma_hp'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">Giảng vi&ecirc;n:
                            {{ $subData['username'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">Lớp:
                            {{ $subData['loai'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">M&atilde; lớp:
                            {{ $subData['ma'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:21.3px'><span style="font-size:21.3px">Địa điểm:
                            {{ $subData['class'] ?? '' }}</span></p>
                </td>
            </tr>
        </tbody>
    </table>
    <table id="customers">

        <tr>
            <th style="width:20pt;border:1.5px solid black;font-size:21.3px">STT</th>
            <th style="width:50pt;border:1.5px solid black; font-size:21.3px">MSSV</th>
            <th style="width:75pt;border:1.5px solid black; font-size:21.3px">Ngày sinh</th>
            <th style="width:120pt;border:1.5px solid black; font-size:21.3px">Họ và tên</th>
            <th style="width:90pt;border:1.5px solid black; font-size:21.3px">Điểm tích cực</th>
            <th style="width:60pt;border:1.5px solid black; font-size:21.3px">Ghi chú</th>
        </tr>
        @foreach ($studentData as $student)
            <tr>
                <td style="font-size:19px;border:1.5px solid black;text-align: center">
                    {{ $student['pivot']['stt'] ?? '' }}</td>
                <td style="font-size:19px;border:1.5px solid black">{{ $student['mssv'] ?? '' }}</td>
                <td style="font-size:19px;border:1.5px solid black">
                    {{ \Carbon\Carbon::parse($student['birthday'] ?? '')->format('Y-m-d') }}</td>
                <td style="font-size:19px;border:1.5px solid black">{{ $student['name'] ?? '' }}</td>
                <td style="font-size:19px;border:1.5px solid black"></td>
                <td style="font-size:19px;border:1.5px solid black"></td>
            </tr>
        @endforeach


    </table>
</body>

</html>
