package com.realestate.app.util;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;
import java.security.SecureRandom;

public final class CsrfUtil {
    private static final String CSRF_TOKEN = "csrfToken";
    private static final SecureRandom RANDOM = new SecureRandom();

    private CsrfUtil() {
    }

    public static String getToken(HttpServletRequest request) {
        HttpSession session = request.getSession();
        String token = (String) session.getAttribute(CSRF_TOKEN);
        if (token == null) {
            byte[] buffer = new byte[24];
            RANDOM.nextBytes(buffer);
            StringBuilder builder = new StringBuilder();
            for (byte value : buffer) {
                builder.append(String.format("%02x", value));
            }
            token = builder.toString();
            session.setAttribute(CSRF_TOKEN, token);
        }
        return token;
    }

    public static boolean isValid(HttpServletRequest request) {
        String submitted = request.getParameter("_token");
        String stored = (String) request.getSession().getAttribute(CSRF_TOKEN);
        return submitted != null && submitted.equals(stored);
    }
}
