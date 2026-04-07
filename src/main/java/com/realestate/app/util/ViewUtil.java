package com.realestate.app.util;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public final class ViewUtil {
    private static final DateTimeFormatter DATE = DateTimeFormatter.ofPattern("dd MMM yyyy");
    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("dd MMM yyyy, hh:mm a");

    private ViewUtil() {
    }

    public static String currency(double amount) {
        long rounded = Math.round(amount);
        String sign = rounded < 0 ? "-₹" : "₹";
        return sign + indianNumberFormat(Math.abs(rounded));
    }

    private static String indianNumberFormat(long value) {
        String digits = Long.toString(value);

        if (digits.length() <= 3) {
            return digits;
        }

        String lastThree = digits.substring(digits.length() - 3);
        String remaining = digits.substring(0, digits.length() - 3);
        StringBuilder builder = new StringBuilder();

        while (remaining.length() > 2) {
            builder.insert(0, "," + remaining.substring(remaining.length() - 2));
            remaining = remaining.substring(0, remaining.length() - 2);
        }

        if (!remaining.isEmpty()) {
            builder.insert(0, remaining);
        }

        builder.append(",").append(lastThree);
        return builder.toString();
    }

    public static String date(LocalDate value) {
        return value == null ? "Not scheduled" : value.format(DATE);
    }

    public static String dateTime(LocalDateTime value) {
        return value == null ? "N/A" : value.format(DATE_TIME);
    }

    public static String statusClass(String status) {
        if (status == null) {
            return "muted";
        }
        switch (status.toLowerCase()) {
            case "approved":
            case "paid":
                return "success";
            case "booked":
            case "confirmed":
            case "completed":
                return "primary";
            case "pending":
                return "warning";
            case "cancelled":
            case "rejected":
            case "failed":
                return "danger";
            default:
                return "muted";
        }
    }

    public static String safe(String value) {
        return value == null ? "" : value;
    }

    public static String html(String value) {
        if (value == null) {
            return "";
        }
        return value
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#39;");
    }
}
