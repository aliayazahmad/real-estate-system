package com.realestate.app.web;

import com.realestate.app.dao.UserDao;
import com.realestate.app.model.User;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.PasswordUtil;
import com.realestate.app.util.SessionUtil;
import com.realestate.app.util.ValidationUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@WebServlet(name = "AuthServlet", urlPatterns = {"/login", "/register", "/logout"})
public class AuthServlet extends BaseServlet {
    private final UserDao userDao = new UserDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if ("/logout".equals(path)) {
            SessionUtil.logout(request);
            FlashUtil.success(request, "You have been logged out.");
            redirect(request, response, "/");
            return;
        }

        if (currentUser(request) != null) {
            redirect(request, response, "/dashboard");
            return;
        }

        render(request, response, "/login".equals(path) ? "login.jsp" : "register.jsp");
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if (!validateCsrf(request, response)) {
            return;
        }
        if ("/login".equals(path)) {
            handleLogin(request, response);
        } else if ("/register".equals(path)) {
            handleRegister(request, response);
        }
    }

    private void handleLogin(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String email = request.getParameter("email");
        String password = request.getParameter("password");

        Map<String, String> form = new LinkedHashMap<>();
        form.put("email", email == null ? "" : email.trim());
        List<String> errors = new ArrayList<>();

        if (!ValidationUtil.isEmail(email)) {
            errors.add("Enter a valid email address.");
        }
        if (password == null || password.trim().isEmpty()) {
            errors.add("Password is required.");
        }

        if (errors.isEmpty()) {
            Optional<User> userOptional = userDao.findByEmail(email.trim().toLowerCase());
            if (!userOptional.isPresent() || !PasswordUtil.verifyPassword(password, userOptional.get().getPasswordHash())) {
                errors.add("Email or password is incorrect.");
            } else {
                SessionUtil.login(request, userDao.toSessionUser(userOptional.get()));
                FlashUtil.success(request, "Welcome back, " + userOptional.get().getName() + ".");
                redirect(request, response, "/dashboard");
                return;
            }
        }

        request.setAttribute("form", form);
        request.setAttribute("errors", errors);
        render(request, response, "login.jsp");
    }

    private void handleRegister(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Map<String, String> form = new LinkedHashMap<>();
        form.put("name", safe(request.getParameter("name")));
        form.put("email", safe(request.getParameter("email")).toLowerCase());
        form.put("phone", safe(request.getParameter("phone")));
        form.put("role", safe(request.getParameter("role")).isEmpty() ? "customer" : safe(request.getParameter("role")));
        String password = safe(request.getParameter("password"));
        String confirmPassword = safe(request.getParameter("confirmPassword"));

        List<String> errors = new ArrayList<>();
        if (form.get("name").isEmpty()) {
            errors.add("Full name is required.");
        }
        if (!ValidationUtil.isEmail(form.get("email"))) {
            errors.add("Enter a valid email address.");
        }
        if (!ValidationUtil.isPhone(form.get("phone"))) {
            errors.add("Phone number must be a valid 10-digit Indian mobile number.");
        }
        if (!"customer".equals(form.get("role")) && !"agent".equals(form.get("role"))) {
            errors.add("Choose a valid account type.");
        }
        if (!ValidationUtil.isStrongPassword(password)) {
            errors.add("Password must be at least 8 characters long.");
        }
        if (!password.equals(confirmPassword)) {
            errors.add("Password confirmation does not match.");
        }
        if (userDao.findByEmail(form.get("email")).isPresent()) {
            errors.add("That email address is already registered.");
        }

        if (errors.isEmpty()) {
            User user = new User();
            user.setName(form.get("name"));
            user.setEmail(form.get("email"));
            user.setPhone(form.get("phone"));
            user.setRole(form.get("role"));
            user.setPasswordHash(PasswordUtil.hashPassword(password));
            userDao.create(user);
            FlashUtil.success(request, "Account created successfully. You can log in now.");
            redirect(request, response, "/login");
            return;
        }

        request.setAttribute("form", form);
        request.setAttribute("errors", errors);
        render(request, response, "register.jsp");
    }

    private String safe(String value) {
        return value == null ? "" : value.trim();
    }
}
