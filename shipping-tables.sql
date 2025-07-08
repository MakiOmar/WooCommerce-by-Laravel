-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 08, 2025 at 11:41 AM
-- Server version: 10.4.17-MariaDB-1:10.4.17+maria~focal-log
-- PHP Version: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wordpress`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp3od_woocommerce_shipping_zones`
--

CREATE TABLE `wp3od_woocommerce_shipping_zones` (
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `zone_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_order` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wp3od_woocommerce_shipping_zones`
--

INSERT INTO `wp3od_woocommerce_shipping_zones` (`zone_id`, `zone_name`, `zone_order`) VALUES
(4, 'دول الخليج', 0),
(10, 'المملكة العربية السعودية (بريدة)', 5),
(18, 'JO+LB', 1),
(19, 'AL+DZ+AM+AZ+EG+FI+FR+DE+GR+HU+IS+IT+LU+MC+MA+NO+PT+RU+ES+SE+TR+GB+CH', 2),
(20, 'CA+US+AU+HK+JP+MV+NZ+SG', 3),
(21, 'AW+IQ+LY+ZA+SD+SY+TN', 4),
(25, 'المملكة العربية السعودية (Aramex + سمسا + Fastlo)', 6),
(26, 'المملكة العربية السعودية (سمسا + Fastlo)', 7),
(27, 'المملكة العربية السعودية (سمسا + Aramex)', 8),
(30, 'المملكة العربية السعودية (سمسا)', 9),
(31, 'Japan', 0);

-- --------------------------------------------------------

--
-- Table structure for table `wp3od_woocommerce_shipping_zone_locations`
--

CREATE TABLE `wp3od_woocommerce_shipping_zone_locations` (
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `location_code` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wp3od_woocommerce_shipping_zone_locations`
--

INSERT INTO `wp3od_woocommerce_shipping_zone_locations` (`location_id`, `zone_id`, `location_code`, `location_type`) VALUES
(884, 4, 'AE', 'country'),
(885, 4, 'BH', 'country'),
(886, 4, 'KW', 'country'),
(887, 4, 'OM', 'country'),
(888, 4, 'QA', 'country'),
(1479, 18, 'JO', 'country'),
(1480, 18, 'LB', 'country'),
(1534, 21, 'LY', 'country'),
(1535, 21, 'ZA', 'country'),
(1536, 21, 'SD', 'country'),
(1537, 21, 'TN', 'country'),
(1538, 21, 'IQ', 'country'),
(1539, 21, 'SY', 'country'),
(1540, 21, 'AW', 'country'),
(2189, 27, 'SA:Aba Alworood', 'state'),
(2190, 27, 'SA:Ushayqir', 'state'),
(2191, 27, 'SA:Umluj', 'state'),
(2192, 27, 'SA:Abqaiq', 'state'),
(2193, 27, 'SA:Artawiah', 'state'),
(2194, 27, 'SA:Aflaj', 'state'),
(2195, 27, 'SA:Baha', 'state'),
(2196, 27, 'SA:Al Batra', 'state'),
(2197, 27, 'SA:Al Bijadyah', 'state'),
(2198, 27, 'SA:Al Bashayer', 'state'),
(2199, 27, 'SA:Al-Jsh', 'state'),
(2200, 27, 'SA:Jouf', 'state'),
(2201, 27, 'SA:Hadeethah', 'state'),
(2202, 27, 'SA:Hareeq', 'state'),
(2203, 27, 'SA:Khurma', 'state'),
(2204, 27, 'SA:Al Khishaybi', 'state'),
(2205, 27, 'SA:Khafji', 'state'),
(2206, 27, 'SA:Addayer', 'state'),
(2207, 27, 'SA:Dere\'iyeh', 'state'),
(2208, 27, 'SA:Al Dalemya', 'state'),
(2209, 27, 'SA:Dawadmi', 'state'),
(2210, 27, 'SA:Rwaydah', 'state'),
(2211, 27, 'SA:As Sulaimaniyah', 'state'),
(2212, 27, 'SA:Sulaiyl', 'state'),
(2213, 27, 'SA:Ash Shimasiyah', 'state'),
(2214, 27, 'SA:Ad Dubaiyah', 'state'),
(2215, 27, 'SA:Othmanyah', 'state'),
(2216, 27, 'SA:Oula', 'state'),
(2217, 27, 'SA:Oyaynah', 'state'),
(2218, 27, 'SA:Al Fuwaileq / Ar Rishawiyah', 'state'),
(2219, 27, 'SA:Qurayat', 'state'),
(2220, 27, 'SA:Qunfudah', 'state'),
(2221, 27, 'SA:Quwei\'ieh', 'state'),
(2222, 27, 'SA:Majarda', 'state'),
(2223, 27, 'SA:Al Moya', 'state'),
(2224, 27, 'SA:Alnabhanya', 'state'),
(2225, 27, 'SA:Noweirieh', 'state'),
(2226, 27, 'SA:Alhada', 'state'),
(2227, 27, 'SA:Wajeh (Al Wajh)', 'state'),
(2228, 27, 'SA:Bader', 'state'),
(2229, 27, 'SA:Baqaa', 'state'),
(2230, 27, 'SA:BilJurashi', 'state'),
(2231, 27, 'SA:Bisha', 'state'),
(2232, 27, 'SA:Tatleeth', 'state'),
(2233, 27, 'SA:Turba', 'state'),
(2234, 27, 'SA:Thumair', 'state'),
(2235, 27, 'SA:Tanjeeb', 'state'),
(2236, 27, 'SA:Tanda', 'state'),
(2237, 27, 'SA:Tayma', 'state'),
(2238, 27, 'SA:Thadek', 'state'),
(2239, 27, 'SA:Tharmada', 'state'),
(2240, 27, 'SA:Jalajel', 'state'),
(2241, 27, 'SA:Halat Ammar', 'state'),
(2242, 27, 'SA:Huraymala', 'state'),
(2243, 27, 'SA:Hafer Al Batin', 'state'),
(2244, 27, 'SA:Haqil', 'state'),
(2245, 27, 'SA:Hotat Sudair', 'state'),
(2246, 27, 'SA:Khaibar', 'state'),
(2247, 27, 'SA:Domat Al Jandal', 'state'),
(2248, 27, 'SA:Deraab', 'state'),
(2249, 27, 'SA:Rejal Alma\'a', 'state'),
(2250, 27, 'SA:Rahima', 'state'),
(2251, 27, 'SA:Rafha', 'state'),
(2252, 27, 'SA:Remah', 'state'),
(2253, 27, 'SA:Rowdat Sodair', 'state'),
(2254, 27, 'SA:Sajir', 'state'),
(2255, 27, 'SA:Sabt El Alaya', 'state'),
(2256, 27, 'SA:Sakaka', 'state'),
(2257, 27, 'SA:Salwa', 'state'),
(2258, 27, 'SA:Simira', 'state'),
(2259, 27, 'SA:Sharourah', 'state'),
(2260, 27, 'SA:Shaqra', 'state'),
(2261, 27, 'SA:Sarar', 'state'),
(2262, 27, 'SA:Duba', 'state'),
(2263, 27, 'SA:Dariyah', 'state'),
(2264, 27, 'SA:Turaif', 'state'),
(2265, 27, 'SA:Arar', 'state'),
(2266, 27, 'SA:Afif', 'state'),
(2267, 27, 'SA:Uqlat Al Suqur', 'state'),
(2268, 27, 'SA:Ain Dar', 'state'),
(2269, 27, 'SA:Farasan', 'state'),
(2270, 27, 'SA:Qariya Al Olaya', 'state'),
(2271, 27, 'SA:Qusayba', 'state'),
(2272, 27, 'SA:Mrat', 'state'),
(2273, 27, 'SA:Mastura', 'state'),
(2274, 27, 'SA:Mulayh', 'state'),
(2275, 27, 'SA:Mahad Al Dahab', 'state'),
(2276, 27, 'SA:Mawqaq', 'state'),
(2277, 27, 'SA:Najran', 'state'),
(2278, 27, 'SA:Wadi El Dwaser', 'state'),
(2543, 26, 'SA:أحد المسارحة', 'state'),
(2544, 26, 'SA:الأسياح', 'state'),
(2545, 26, 'SA:الجفر', 'state'),
(2546, 26, 'SA:الخبراء', 'state'),
(2547, 26, 'SA:الدرب', 'state'),
(2548, 26, 'SA:السيل الكبير', 'state'),
(2549, 26, 'SA:النماص', 'state'),
(2550, 26, 'SA:تنومة', 'state'),
(2551, 26, 'SA:ذهبان', 'state'),
(2552, 26, 'SA:سرات عبيدة', 'state'),
(2553, 26, 'SA:شقيق', 'state'),
(2554, 26, 'SA:ضمد', 'state'),
(2555, 26, 'SA:مبرز', 'state'),
(2556, 26, 'SA:مدينة الملك عبدالله الاقتصادية', 'state'),
(2557, 26, 'SA:وادي بن هشبل', 'state'),
(3048, 10, 'SA:Buraidah', 'state'),
(3049, 25, 'SA:Abha', 'state'),
(3050, 25, 'SA:Abu Areish', 'state'),
(3051, 25, 'SA:Ahad Rufaidah', 'state'),
(3052, 25, 'SA:Al Hassa', 'state'),
(3053, 25, 'SA:Badaya', 'state'),
(3054, 25, 'SA:Bukeiriah', 'state'),
(3055, 25, 'SA:Jubail', 'state'),
(3056, 25, 'SA:Jumum', 'state'),
(3057, 25, 'SA:Hinakeya', 'state'),
(3058, 25, 'SA:Khobar', 'state'),
(3059, 25, 'SA:Kharj', 'state'),
(3060, 25, 'SA:Daelim', 'state'),
(3061, 25, 'SA:Dammam', 'state'),
(3062, 25, 'SA:AlRass', 'state'),
(3063, 25, 'SA:Riyadh', 'state'),
(3064, 25, 'SA:Zulfi', 'state'),
(3065, 25, 'SA:Taif', 'state'),
(3066, 25, 'SA:Dhahran', 'state'),
(3067, 25, 'SA:Uyun', 'state'),
(3068, 25, 'SA:Alghat', 'state'),
(3069, 25, 'SA:Qatif', 'state'),
(3070, 25, 'SA:Majma', 'state'),
(3071, 25, 'SA:Madinah', 'state'),
(3072, 25, 'SA:Midinhab', 'state'),
(3073, 25, 'SA:Muzahmiah', 'state'),
(3074, 25, 'SA:Hofuf', 'state'),
(3075, 25, 'SA:Bahara', 'state'),
(3076, 25, 'SA:Buraidah', 'state'),
(3077, 25, 'SA:Baqiq', 'state'),
(3078, 25, 'SA:Balahmar', 'state'),
(3079, 25, 'SA:Balasmar', 'state'),
(3080, 25, 'SA:Bish', 'state'),
(3081, 25, 'SA:Tarut', 'state'),
(3082, 25, 'SA:Tabuk', 'state'),
(3083, 25, 'SA:Towal', 'state'),
(3084, 25, 'SA:Gizan', 'state'),
(3085, 25, 'SA:Jeddah', 'state'),
(3086, 25, 'SA:Hail', 'state'),
(3087, 25, 'SA:Hawtat Bani Tamim', 'state'),
(3088, 25, 'SA:Khamis Mushait', 'state'),
(3089, 25, 'SA:Rabigh', 'state'),
(3090, 25, 'SA:Ras Tanura', 'state'),
(3091, 25, 'SA:Riyadh Al Khabra', 'state'),
(3092, 25, 'SA:Seihat', 'state'),
(3093, 25, 'SA:Samtah', 'state'),
(3094, 25, 'SA:Sabya', 'state'),
(3095, 25, 'SA:Safwa', 'state'),
(3096, 25, 'SA:Asfan', 'state'),
(3097, 25, 'SA:Anak', 'state'),
(3098, 25, 'SA:Onaiza', 'state'),
(3099, 25, 'SA:Oyoon Al Jawa', 'state'),
(3100, 25, 'SA:Mohayel Aseer', 'state'),
(3101, 25, 'SA:Makkah', 'state'),
(3102, 25, 'SA:Yanbu', 'state'),
(3287, 20, 'HK', 'country'),
(3288, 20, 'MV', 'country'),
(3289, 20, 'SG', 'country'),
(3290, 20, 'CA', 'country'),
(3291, 20, 'US', 'country'),
(3292, 20, 'AU', 'country'),
(3293, 20, 'NZ', 'country'),
(3294, 31, 'JP', 'country'),
(3577, 19, 'DZ', 'country'),
(3578, 19, 'EG', 'country'),
(3579, 19, 'MA', 'country'),
(3580, 19, 'AM', 'country'),
(3581, 19, 'AZ', 'country'),
(3582, 19, 'AL', 'country'),
(3583, 19, 'BE', 'country'),
(3584, 19, 'FI', 'country'),
(3585, 19, 'FR', 'country'),
(3586, 19, 'DE', 'country'),
(3587, 19, 'GR', 'country'),
(3588, 19, 'HU', 'country'),
(3589, 19, 'IS', 'country'),
(3590, 19, 'IT', 'country'),
(3591, 19, 'LU', 'country'),
(3592, 19, 'MC', 'country'),
(3593, 19, 'NO', 'country'),
(3594, 19, 'PT', 'country'),
(3595, 19, 'RU', 'country'),
(3596, 19, 'ES', 'country'),
(3597, 19, 'SE', 'country'),
(3598, 19, 'CH', 'country'),
(3599, 19, 'TR', 'country'),
(3600, 19, 'GB', 'country'),
(3601, 19, 'IE', 'country'),
(3602, 19, 'NL', 'country'),
(3603, 30, 'SA:ابيار الماشي', 'state'),
(3604, 30, 'SA:الأطاولة', 'state'),
(3605, 30, 'SA:الاكحل', 'state'),
(3606, 30, 'SA:الاوجام', 'state'),
(3607, 30, 'SA:البطحاء', 'state'),
(3608, 30, 'SA:البطين', 'state'),
(3609, 30, 'SA:الثقبة', 'state'),
(3610, 30, 'SA:الثمد', 'state'),
(3611, 30, 'SA:الجبيله', 'state'),
(3612, 30, 'SA:الجثامية', 'state'),
(3613, 30, 'SA:الجله', 'state'),
(3614, 30, 'SA:الجيله', 'state'),
(3615, 30, 'SA:الحزم', 'state'),
(3616, 30, 'SA:الحسي', 'state'),
(3617, 30, 'SA:الحلوه', 'state'),
(3618, 30, 'SA:الخشيبية', 'state'),
(3619, 30, 'SA:الخماسين', 'state'),
(3620, 30, 'SA:الدخنة', 'state'),
(3621, 30, 'SA:الدغيمية', 'state'),
(3622, 30, 'SA:الراذيا', 'state'),
(3623, 30, 'SA:الرقي', 'state'),
(3624, 30, 'SA:الريان', 'state'),
(3625, 30, 'SA:الريش', 'state'),
(3626, 30, 'SA:السليمي', 'state'),
(3627, 30, 'SA:السيل الصغير', 'state'),
(3628, 30, 'SA:الشفاء', 'state'),
(3629, 30, 'SA:الشقرة', 'state'),
(3630, 30, 'SA:الشنان', 'state'),
(3631, 30, 'SA:الشهيلي', 'state'),
(3632, 30, 'SA:الشيحية', 'state'),
(3633, 30, 'SA:الصلصلة', 'state'),
(3634, 30, 'SA:الصويدرة', 'state'),
(3635, 30, 'SA:الضلفعة', 'state'),
(3636, 30, 'SA:الضميريه', 'state'),
(3637, 30, 'SA:الطرفية', 'state'),
(3638, 30, 'SA:الطوال', 'state'),
(3639, 30, 'SA:العشيرة', 'state'),
(3640, 30, 'SA:العضيلية', 'state'),
(3641, 30, 'SA:العقير', 'state'),
(3642, 30, 'SA:العقيق', 'state'),
(3643, 30, 'SA:العمار', 'state'),
(3644, 30, 'SA:العمجية', 'state'),
(3645, 30, 'SA:العمران', 'state'),
(3646, 30, 'SA:الغزالة', 'state'),
(3647, 30, 'SA:الفريش', 'state'),
(3648, 30, 'SA:الفوارة', 'state'),
(3649, 30, 'SA:القايد', 'state'),
(3650, 30, 'SA:القراء', 'state'),
(3651, 30, 'SA:القرينه', 'state'),
(3652, 30, 'SA:القصب', 'state'),
(3653, 30, 'SA:القوارة', 'state'),
(3654, 30, 'SA:القيصومة', 'state'),
(3655, 30, 'SA:الليث', 'state'),
(3656, 30, 'SA:المخواة', 'state'),
(3657, 30, 'SA:المدينة الصناعية الثالثة', 'state'),
(3658, 30, 'SA:المدينة العسكرية', 'state'),
(3659, 30, 'SA:المسيجيد', 'state'),
(3660, 30, 'SA:المضيلف', 'state'),
(3661, 30, 'SA:المعشبة', 'state'),
(3662, 30, 'SA:المليلح', 'state'),
(3663, 30, 'SA:المندسة', 'state'),
(3664, 30, 'SA:المندق', 'state'),
(3665, 30, 'SA:المنيفة', 'state'),
(3666, 30, 'SA:المويلح', 'state'),
(3667, 30, 'SA:النقية', 'state'),
(3668, 30, 'SA:الهرارة', 'state'),
(3669, 30, 'SA:الوديان', 'state'),
(3670, 30, 'SA:الوزية', 'state'),
(3671, 30, 'SA:اليتمه', 'state'),
(3672, 30, 'SA:باد', 'state'),
(3673, 30, 'SA:بارق', 'state'),
(3674, 30, 'SA:بدر حنين', 'state'),
(3675, 30, 'SA:بنبان', 'state'),
(3676, 30, 'SA:بني مالك', 'state'),
(3677, 30, 'SA:تاريب', 'state'),
(3678, 30, 'SA:تمرية', 'state'),
(3679, 30, 'SA:ثادج', 'state'),
(3680, 30, 'SA:جسر الملك فهد', 'state'),
(3681, 30, 'SA:جيزان', 'state'),
(3682, 30, 'SA:حايل', 'state'),
(3683, 30, 'SA:حرمة', 'state'),
(3684, 30, 'SA:خبرا', 'state'),
(3685, 30, 'SA:رفايع الجمش', 'state'),
(3686, 30, 'SA:رنية', 'state'),
(3687, 30, 'SA:سايرا', 'state'),
(3688, 30, 'SA:سدير', 'state'),
(3689, 30, 'SA:سيرة', 'state'),
(3690, 30, 'SA:شرما', 'state'),
(3691, 30, 'SA:شقري', 'state'),
(3692, 30, 'SA:شيبة', 'state'),
(3693, 30, 'SA:شيدقم', 'state'),
(3694, 30, 'SA:صلاصل', 'state'),
(3695, 30, 'SA:صلبوخ', 'state'),
(3696, 30, 'SA:ضباء', 'state'),
(3697, 30, 'SA:ضرما', 'state'),
(3698, 30, 'SA:ضيده', 'state'),
(3699, 30, 'SA:طبرجل', 'state'),
(3700, 30, 'SA:طريف القصيم', 'state'),
(3701, 30, 'SA:ظليم', 'state'),
(3702, 30, 'SA:ظهران الجنوب', 'state'),
(3703, 30, 'SA:عثمر', 'state'),
(3704, 30, 'SA:عرعر الجدريدة', 'state'),
(3705, 30, 'SA:عشيرة سدير', 'state'),
(3706, 30, 'SA:عودة سدير', 'state'),
(3707, 30, 'SA:عين ابن فهيد', 'state'),
(3708, 30, 'SA:عين دار الجديدة', 'state'),
(3709, 30, 'SA:عين دار القديمة', 'state'),
(3710, 30, 'SA:فودة', 'state'),
(3711, 30, 'SA:فيد', 'state'),
(3712, 30, 'SA:قصر بن عقيل', 'state'),
(3713, 30, 'SA:قصور ال مقبل', 'state'),
(3714, 30, 'SA:قلوة', 'state'),
(3715, 30, 'SA:قيال', 'state'),
(3716, 30, 'SA:كامل', 'state'),
(3717, 30, 'SA:محايل', 'state'),
(3718, 30, 'SA:مدينة الملك خالد', 'state'),
(3719, 30, 'SA:مطار الدمام', 'state'),
(3720, 30, 'SA:مطار الرياض', 'state'),
(3721, 30, 'SA:مطار القصيم', 'state'),
(3722, 30, 'SA:مطار جدة', 'state'),
(3723, 30, 'SA:ملهم', 'state'),
(3724, 30, 'SA:نعام', 'state'),
(3725, 30, 'SA:نيفي', 'state'),
(3726, 30, 'SA:هيت', 'state'),
(3727, 30, 'SA:وادي الفرع', 'state'),
(3728, 30, 'SA:وادي ريم', 'state'),
(3729, 30, 'SA:يثرب', 'state'),
(3730, 30, 'SA:البرك', 'state'),
(3731, 30, 'SA:الرين', 'state'),
(3732, 30, 'SA:Edabi', 'state');

-- --------------------------------------------------------

--
-- Table structure for table `wp3od_woocommerce_shipping_zone_methods`
--

CREATE TABLE `wp3od_woocommerce_shipping_zone_methods` (
  `zone_id` bigint(20) UNSIGNED NOT NULL,
  `instance_id` bigint(20) UNSIGNED NOT NULL,
  `method_id` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_order` bigint(20) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wp3od_woocommerce_shipping_zone_methods`
--

INSERT INTO `wp3od_woocommerce_shipping_zone_methods` (`zone_id`, `instance_id`, `method_id`, `method_order`, `is_enabled`) VALUES
(0, 5, 'flat_rate', 1, 0),
(4, 8, 'flat_rate', 2, 1),
(4, 24, 'free_shipping', 1, 1),
(4, 25, 'flat_rate', 3, 1),
(10, 48, 'free_shipping', 2, 1),
(18, 55, 'flat_rate', 1, 1),
(19, 56, 'flat_rate', 1, 1),
(20, 57, 'flat_rate', 1, 1),
(21, 58, 'flat_rate', 1, 1),
(10, 68, 'free_shipping', 4, 1),
(25, 69, 'free_shipping', 3, 0),
(25, 70, 'free_shipping', 2, 1),
(25, 71, 'free_shipping', 4, 1),
(25, 72, 'flat_rate', 5, 1),
(25, 73, 'flat_rate', 6, 0),
(25, 74, 'flat_rate', 7, 1),
(25, 75, 'flat_rate', 8, 1),
(25, 76, 'flat_rate', 9, 0),
(25, 77, 'flat_rate', 10, 1),
(26, 78, 'free_shipping', 1, 1),
(26, 79, 'free_shipping', 2, 1),
(26, 80, 'flat_rate', 3, 1),
(26, 81, 'flat_rate', 4, 1),
(26, 82, 'flat_rate', 5, 1),
(26, 83, 'flat_rate', 6, 1),
(27, 84, 'free_shipping', 1, 1),
(27, 85, 'free_shipping', 2, 0),
(27, 86, 'flat_rate', 3, 1),
(27, 87, 'flat_rate', 4, 0),
(27, 88, 'flat_rate', 5, 1),
(27, 89, 'flat_rate', 6, 0),
(30, 93, 'free_shipping', 1, 1),
(30, 94, 'flat_rate', 2, 1),
(30, 95, 'flat_rate', 3, 1),
(10, 99, 'free_shipping', 3, 0),
(25, 102, 'flat_rate', 11, 0),
(25, 103, 'flat_rate', 12, 0),
(26, 104, 'flat_rate', 7, 0),
(26, 105, 'flat_rate', 8, 0),
(27, 106, 'flat_rate', 7, 0),
(30, 107, 'flat_rate', 4, 0),
(10, 108, 'redbox_pickup_delivery', 4, 1),
(25, 109, 'redbox_pickup_delivery', 12, 1),
(26, 110, 'redbox_pickup_delivery', 9, 1),
(27, 111, 'redbox_pickup_delivery', 8, 1),
(30, 112, 'redbox_pickup_delivery', 5, 1),
(31, 113, 'flat_rate', 1, 1),
(10, 116, 'local_pickup', 1, 1),
(10, 117, 'flat_rate', 5, 1),
(10, 119, 'flat_rate', 6, 0),
(10, 120, 'flat_rate', 7, 1),
(10, 121, 'flat_rate', 8, 1),
(10, 122, 'flat_rate', 9, 0),
(10, 123, 'flat_rate', 11, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp3od_woocommerce_shipping_zones`
--
ALTER TABLE `wp3od_woocommerce_shipping_zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- Indexes for table `wp3od_woocommerce_shipping_zone_locations`
--
ALTER TABLE `wp3od_woocommerce_shipping_zone_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `location_type_code` (`location_type`(10),`location_code`(20)),
  ADD KEY `zone_id` (`zone_id`);

--
-- Indexes for table `wp3od_woocommerce_shipping_zone_methods`
--
ALTER TABLE `wp3od_woocommerce_shipping_zone_methods`
  ADD PRIMARY KEY (`instance_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp3od_woocommerce_shipping_zones`
--
ALTER TABLE `wp3od_woocommerce_shipping_zones`
  MODIFY `zone_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `wp3od_woocommerce_shipping_zone_locations`
--
ALTER TABLE `wp3od_woocommerce_shipping_zone_locations`
  MODIFY `location_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3733;

--
-- AUTO_INCREMENT for table `wp3od_woocommerce_shipping_zone_methods`
--
ALTER TABLE `wp3od_woocommerce_shipping_zone_methods`
  MODIFY `instance_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
