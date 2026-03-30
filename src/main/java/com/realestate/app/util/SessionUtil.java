package com.realestate.app.util;

import com.realestate.app.model.SessionUser;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;

public final class SessionUtil {
    public static final String AUTH_USER = "authUser";

    private SessionUtil() {
    }

    public static SessionUser getCurrentUser(HttpServletRequest request) {
        HttpSession session = request.getSession(false);
        if (session == null) {
            return null;
        }
        return (SessionUser) session.getAttribute(AUTH_USER);
    }

    public static void login(HttpServletRequest request, SessionUser sessionUser) {
        request.getSession().setAttribute(AUTH_USER, sessionUser);
    }

    public static void logout(HttpServletRequest request) {
        HttpSession session = request.getSession(false);
        if (session != null) {
            session.invalidate();
        }
    }

    public static boolean hasRole(HttpServletRequest request, String... roles) {
        SessionUser user = getCurrentUser(request);
        if (user == null) {
            return false;
        }
        for (String role : roles) {
            if (role.equalsIgnoreCase(user.getRole())) {
                return true;
            }
        }
        return false;
    }
}
