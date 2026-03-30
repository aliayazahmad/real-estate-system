package com.realestate.app.web;

import com.realestate.app.model.SessionUser;
import com.realestate.app.util.CsrfUtil;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.SessionUtil;

import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

public abstract class BaseServlet extends HttpServlet {
    protected void render(HttpServletRequest request, HttpServletResponse response, String view) throws ServletException, IOException {
        FlashUtil.consume(request);
        request.setAttribute("csrfToken", CsrfUtil.getToken(request));
        request.setAttribute("currentUser", SessionUtil.getCurrentUser(request));
        RequestDispatcher dispatcher = request.getRequestDispatcher("/WEB-INF/views/" + view);
        dispatcher.forward(request, response);
    }

    protected void redirect(HttpServletRequest request, HttpServletResponse response, String path) throws IOException {
        response.sendRedirect(request.getContextPath() + path);
    }

    protected SessionUser currentUser(HttpServletRequest request) {
        return SessionUtil.getCurrentUser(request);
    }

    protected boolean requireLogin(HttpServletRequest request, HttpServletResponse response) throws IOException {
        if (currentUser(request) == null) {
            FlashUtil.error(request, "Please log in to continue.");
            redirect(request, response, "/login");
            return false;
        }
        return true;
    }

    protected boolean requireRole(HttpServletRequest request, HttpServletResponse response, String... roles) throws IOException {
        if (!requireLogin(request, response)) {
            return false;
        }
        if (!SessionUtil.hasRole(request, roles)) {
            FlashUtil.error(request, "You do not have permission to access that page.");
            redirect(request, response, "/dashboard");
            return false;
        }
        return true;
    }

    protected boolean validateCsrf(HttpServletRequest request, HttpServletResponse response) throws IOException {
        if (!CsrfUtil.isValid(request)) {
            FlashUtil.error(request, "Security token mismatch. Refresh the page and try again.");
            String referer = request.getHeader("Referer");
            response.sendRedirect(referer != null ? referer : request.getContextPath() + "/");
            return false;
        }
        return true;
    }
}
