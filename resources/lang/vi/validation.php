<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
     */

    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'active_url' => 'Trường :attribute không phải là một URL hợp lệ.',
    'after' => 'Trường :attribute phải là một ngày sau ngày :date.',
    'after_or_equal' => 'Trường :attribute phải là một ngày sau hoặc bằng ngày :date.',
    'alpha' => 'Trường :attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash' => 'Trường :attribute chỉ có thể chứa chữ cái, số và dấu gạch ngang.',
    'alpha_num' => 'Trường :attribute chỉ có thể chứa chữ cái và số.',
    'array' => 'Kiểu dữ liệu của trường :attribute phải là dạng mảng.',
    'before' => 'Trường :attribute phải là một ngày trước ngày :date.',
    'before_or_equal' => 'Trường :attribute phải là một ngày trước hoặc bằng ngày :date.',
    'between' => [
        'numeric' => 'Trường :attribute phải nằm trong khoảng :min - :max.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải từ :min - :max kB.',
        'string' => 'Trường :attribute phải từ :min - :max ký tự.',
        'array' => 'Trường :attribute phải có từ :min - :max phần tử.',
    ],
    'boolean' => 'Trường :attribute phải là true hoặc false.',
    'confirmed' => 'Giá trị xác nhận trong trường :attribute không khớp.',
    'date' => 'Trường :attribute không phải là định dạng của ngày-tháng.',
    'date_equals' => 'Trường :attribute phải là một ngày bằng với :date.',
    'date_format' => 'Trường :attribute không giống với định dạng :format.',
    'different' => 'Trường :attribute và :other phải khác nhau.',
    'digits' => 'Độ dài của trường :attribute phải gồm :digits chữ số.',
    'digits_between' => 'Độ dài của trường :attribute phải nằm trong khoảng :min and :max chữ số.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'ends_with' => 'Trường :attribute phải kết thúc bằng một trong những giá trị sau: :values',
    'exists' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'file' => 'Trường :attribute phải là một tập tin.',
    'filled' => 'Trường :attribute không được bỏ trống.',
    'gt' => [
        'numeric' => 'Trường :attribute phải lớn hơn :max.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải lớn hơn :max KB.',
        'string' => 'Trường :attribute phải lớn hơn :max ký tự.',
        'array' => 'Trường :attribute phải lớn hơn :max phần tử.',
    ],
    'gte' => [
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :max.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải lớn hơn hoặc bằng :max KB.',
        'string' => 'Trường :attribute phải lớn hơn hoặc bằng :max ký tự.',
        'array' => 'Trường :attribute phải lớn hơn hoặc bằng :max phần tử.',
    ],
    'image' => 'Các tập tin trong trường :attribute phải là định dạng hình ảnh.',
    'in' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'Trường :attribute phải là một số nguyên.',
    'ip' => 'Trường :attribute phải là một địa chỉa IP.',
    'ipv4' => 'Trường :attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => 'Trường :attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => 'Trường :attribute phải là chuỗi JSON hợp lệ.',
    'lt' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn là :min.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải nhỏ hơn :min KB.',
        'string' => 'Trường :attribute phải có nhỏ hơn :min ký tự.',
        'array' => 'Trường :attribute phải có nhỏ hơn :min phần tử.',
    ],
    'lte' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn hoặc bằng là :min.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải nhỏ hơn hoặc bằng :min KB.',
        'string' => 'Trường :attribute phải có nhỏ hơn hoặc bằng :min ký tự.',
        'array' => 'Trường :attribute phải có nhỏ hơn hoặc bằng :min phần tử.',
    ],
    'max' => [
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'file' => 'Dung lượng tập tin trong trường :attribute không được lớn hơn :max KB.',
        'string' => 'Trường :attribute không được lớn hơn :max ký tự.',
        'array' => 'Trường :attribute không được lớn hơn :max phần tử.',
    ],
    'mimes' => 'Trường :attribute phải là một tập tin có định dạng: :values.',
    'mimetypes' => 'Trường :attribute phải là một tệp có định dạng là: :values.',
    'min' => [
        'numeric' => 'Trường :attribute không được bé hơn :min.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải tối thiểu :min KB.',
        'string' => 'Trường :attribute phải có tối thiểu :min ký tự.',
        'array' => 'Trường :attribute phải có tối thiểu :min phần tử.',
    ],
    'not_in' => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'not_regex' => 'Trường :attribute định dạng không hợp lệ.',
    'numeric' => 'Trường :attribute phải là một số.',
    'present' => 'The :attribute field must be present.',
    'regex' => 'Định dạng trường :attribute không hợp lệ.',
    'required' => 'Trường :attribute không được bỏ trống.',
    'required_if' => 'Trường :attribute không được bỏ trống khi trường :other là :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'Trường :attribute không được bỏ trống khi trường :values có giá trị.',
    'required_with_all' => 'The :attribute field is required when :values is present.',
    'required_without' => 'Trường :attribute không được bỏ trống khi trường :values không có giá trị.',
    'required_without_all' => 'Trường :attribute không được bỏ trống khi tất cả :values không có giá trị.',
    'same' => 'Trường :attribute và :other phải giống nhau.',
    'size' => [
        'numeric' => 'Trường :attribute phải bằng :size.',
        'file' => 'Dung lượng tập tin trong trường :attribute phải bằng :size kB.',
        'string' => 'Trường :attribute phải chứa :size ký tự.',
        'array' => 'Trường :attribute phải chứa :size phần tử.',
    ],
    'starts_with' => 'Trường :attribute phải được bắt đầu bằng một trong những giá trị sau: :values',
    'string' => 'Trường :attribute phải là một chuỗi.',
    'timezone' => 'Trường :attribute phải là một múi giờ hợp lệ.',
    'unique' => 'Trường :attribute đã có trong hệ thống.',
    'uploaded' => 'Trường :attribute không thể tải lên.',
    'url' => 'Trường :attribute không giống với định dạng một URL.',
    'uuid' => 'Trường :attribute không phải là một chuỗi UUID hợp lẹ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
     */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
     */

    'attributes' => [
        'name' => 'tên',
        'ten' => 'tên',
        'dinh_danh' => 'Định danh',
        'username' => 'tên đăng nhập',
        'email' => 'email',
        'first_name' => 'tên',
        'last_name' => 'họ',
        'password' => 'mật khẩu',
        'password_confirmation' => 'Mật khẩu xác nhận',
        'city' => 'thành phố',
        'country' => 'quốc gia',
        'address' => 'địa chỉ',
        'phone' => 'số điện thoại',
        'mobile' => 'di động',
        'birthday' => 'ngày sinh nhật',
        'age' => 'tuổi',
        'sex' => 'giới tính',
        'gender' => 'giới tính',
        'year' => 'năm',
        'month' => 'tháng',
        'day' => 'ngày',
        'hour' => 'giờ',
        'minute' => 'phút',
        'second' => 'giây',
        'title' => 'tiêu đề',
        'content' => 'nội dung',
        'body' => 'nội dung',
        'description' => 'mô tả',
        'excerpt' => 'trích dẫn',
        'date' => 'ngày',
        'time' => 'thời gian',
        'subject' => 'tiêu đề',
        'message' => 'lời nhắn',
        'available' => 'có sẵn',
        'size' => 'kích thước',
        'code' => 'Mã',
        'data_source_ids' => "nguồn dữ liệu truy vấn",
        'data_source_ids.*' => "nguồn dữ liệu truy vấn",
        'layer_ids' => "lớp đối tượng truy vấn",
        'layer_ids.*' => "lớp đối tượng truy vấn",
        'confirm_password' => "mật khẩu xác nhận",
        'image' =>
        "file ảnh",
        'group_id' => 'nhóm dữ liệu',
        'file' => 'tập tin',
        'cap_bac' => 'cấp bậc',
        'chuc_vu' => 'chức vụ',
        'dia_chi' => 'địa chỉ',
        'cmnd' => 'CMND/CCCD'
    ],
    'not_exists' => ':attribute đã tồn tại.',
    'currentpassword' => 'Mật khẩu không đúng',
    'password' => 'Mật khẩu không đúng.',
    'user_not_active' => 'Tài khoản đã bị khóa. Liên hệ quản trị hệ thống để biết thông tin chi tiết',

];
