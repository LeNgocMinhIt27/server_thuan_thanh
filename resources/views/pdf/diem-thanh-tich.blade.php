<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DANH SÁCH SINH VIÊN ĐIỂM DANH</title>
    <style>
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
            text-align: left;
            color: black;
        }

        body {
            font-family: DejaVu Sans, sans-serif !important;
        }
    </style>
</head>

<body>
    <h2 class="main_title">DANH S&Aacute;CH SINH VI&Ecirc;N</h2>
    <table style="width:499.5pt;margin-left:-9.0pt;border-collapse:collapse;border:none;">
        <tbody>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Học phần:
                            {{ $subData['ten_hp'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">M&atilde; học phần:
                            {{ $subData['ma_hp'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Giảng vi&ecirc;n:
                            {{ $subData['username'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Lớp:
                            {{ $subData['loai'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">M&atilde; lớp:
                            {{ $subData['ma'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Địa điểm:
                            {{ $subData['class'] ?? '' }}</span></p>
                </td>
            </tr>
        </tbody>
    </table>
    <table id="customers">


        <tr>
            <th style="width:30pt;border:1.5px solid black">STT</th>
            <th style="width:70pt;border:1.5px solid black">MSSV</th>
            {{-- <th style="width:70pt;border:1.5px solid black">Ngày sinh</th> --}}
            <th style="width:140pt;border:1.5px solid black">Họ và tên</th>
            <th style="width:80pt;border:1.5px solid black">Điểm tích cực</th>
            <th style="width:90pt;border:1.5px solid black">Ghi chú</th>

        </tr>
        @foreach ($studentData as $student)
            <tr>
                <td style="border:1.5px solid black;text-align: right">{{ $student['pivot']['stt'] ?? '' }}</td>
                <td style="border:1.5px solid black">{{ $student['mssv'] ?? '' }}</td>
                {{-- <td style="border:1.5px solid black">{{ \Carbon\Carbon::parse($student['birthday'] ?? '')->format('Y-m-d') }}</td> --}}
                <td style="border:1.5px solid black">{{ $student['name'] ?? '' }}</td>
                <td style="border:1.5px solid black"></td>
                <td style="border:1.5px solid black"></td>
            </tr>
        @endforeach


    </table>
</body>

</html>
