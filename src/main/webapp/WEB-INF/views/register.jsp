<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
List<String> errors = (List<String>) request.getAttribute("errors");
Map<String, String> form = (Map<String, String>) request.getAttribute("form");
if (form == null) {
    form = new java.util.LinkedHashMap<String, String>();
}
request.setAttribute("pageTitle", "Register");
%>
<%@ include file="partials/header.jspf" %>

<section class="auth-shell">
    <div class="auth-intro">
        <span class="eyebrow">Get started</span>
        <h1>Create a customer or agent account.</h1>
        <p>Role-based access keeps the experience focused for customers, agents, and administrators.</p>
    </div>
    <div class="form-card">
        <h2>Register</h2>
        <% if (errors != null && !errors.isEmpty()) { %>
            <div class="inline-errors">
                <% for (String error : errors) { %>
                    <p><%= ViewUtil.html(error) %></p>
                <% } %>
            </div>
        <% } %>
        <form method="post" action="<%= contextPath %>/register">
            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
            <label>Full name</label>
            <input type="text" name="name" required value="<%= ViewUtil.html(form.get("name")) %>">

            <label>Email address</label>
            <input type="email" name="email" required value="<%= ViewUtil.html(form.get("email")) %>">

            <label>Phone number</label>
            <input type="text" name="phone" placeholder="9876543210" value="<%= ViewUtil.html(form.get("phone")) %>">

            <label>Account type</label>
            <select name="role">
                <option value="customer" <%= "customer".equals(form.get("role")) || form.get("role") == null ? "selected" : "" %>>Customer</option>
                <option value="agent" <%= "agent".equals(form.get("role")) ? "selected" : "" %>>Agent</option>
            </select>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm password</label>
            <input type="password" name="confirmPassword" required>

            <button class="btn btn-primary btn-block" type="submit">Create account</button>
        </form>
        <p class="form-note">Already registered? <a href="<%= contextPath %>/login">Login here</a>.</p>
    </div>
</section>

<%@ include file="partials/footer.jspf" %>
