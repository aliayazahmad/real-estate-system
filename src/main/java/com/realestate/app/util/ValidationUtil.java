package com.realestate.app.util;

import java.time.LocalDate;
import java.util.regex.Pattern;

public final class ValidationUtil {
    private static final Pattern EMAIL_PATTERN = Pattern.compile("^[A-Za-z0-9+_.-]+@[A-Za-z0-9.-]+$");
    private static final Pattern PHONE_PATTERN = Pattern.compile("^[6-9][0-9]{9}$");

    private ValidationUtil() {
    }

    public static boolean isEmail(String email) {
        return email != null && EMAIL_PATTERN.matcher(email.trim()).matches();
    }

    public static boolean isPhone(String phone) {
        return phone == null || phone.trim().isEmpty() || PHONE_PATTERN.matcher(phone.trim()).matches();
    }

    public static boolean isStrongPassword(String password) {
        return password != null && password.length() >= 8;
    }

    public static boolean isPositiveAmount(String value) {
        try {
            return Double.parseDouble(value) > 0;
        } catch (Exception exception) {
            return false;
        }
    }

    public static Integer parseInteger(String value) {
        if (value == null || value.trim().isEmpty()) {
            return null;
        }
        return Integer.parseInt(value.trim());
    }

    public static LocalDate parseDate(String value) {
        if (value == null || value.trim().isEmpty()) {
            return null;
        }
        return LocalDate.parse(value.trim());
    }
}
