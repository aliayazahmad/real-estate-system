package com.realestate.app.util;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;

public final class FlashUtil {
    private static final String FLASH_TYPE = "flashType";
    private static final String FLASH_MESSAGE = "flashMessage";

    private FlashUtil() {
    }

    public static void success(HttpServletRequest request, String message) {
        set(request, "success", message);
    }

    public static void error(HttpServletRequest request, String message) {
        set(request, "danger", message);
    }

    public static void set(HttpServletRequest request, String type, String message) {
        HttpSession session = request.getSession();
        session.setAttribute(FLASH_TYPE, type);
        session.setAttribute(FLASH_MESSAGE, message);
    }

    public static void consume(HttpServletRequest request) {
        HttpSession session = request.getSession(false);
        if (session == null) {
            return;
        }
        request.setAttribute("flashType", session.getAttribute(FLASH_TYPE));
        request.setAttribute("flashMessage", session.getAttribute(FLASH_MESSAGE));
        session.removeAttribute(FLASH_TYPE);
        session.removeAttribute(FLASH_MESSAGE);
    }
}
