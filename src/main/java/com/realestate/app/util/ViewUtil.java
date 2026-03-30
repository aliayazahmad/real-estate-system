package com.realestate.app.util;

import java.text.DecimalFormat;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public final class ViewUtil {
    private static final DecimalFormat MONEY = new DecimalFormat("#,##0.00");
    private static final DateTimeFormatter DATE = DateTimeFormatter.ofPattern("dd MMM yyyy");
    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("dd MMM yyyy, hh:mm a");

    private ViewUtil() {
    }

    public static String currency(double amount) {
        return "Rs. " + MONEY.format(amount);
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
