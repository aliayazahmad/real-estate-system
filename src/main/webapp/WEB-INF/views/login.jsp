<%@ page import="java.util.List" %>
<%@ page import="java.util.Map" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
List<String> errors = (List<String>) request.getAttribute("errors");
Map<String, String> form = (Map<String, String>) request.getAttribute("form");
if (form == null) {
    form = new java.util.LinkedHashMap<String, String>();
}
request.setAttribute("pageTitle", "Login");
%>
<%@ include file="partials/header.jspf" %>

<section class="auth-shell">
    <div class="auth-intro">
        <span class="eyebrow">Welcome back</span>
        <h1>Sign in to continue with property operations.</h1>
        <p>Customers can manage bookings and payments. Agents can publish listings. Admins control approvals and reporting.</p>
    </div>
    <div class="form-card">
        <h2>Login</h2>
        <% if (errors != null && !errors.isEmpty()) { %>
            <div class="inline-errors">
                <% for (String error : errors) { %>
                    <p><%= ViewUtil.html(error) %></p>
                <% } %>
            </div>
        <% } %>
        <form method="post" action="<%= contextPath %>/login">
            <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
            <label>Email address</label>
            <input type="email" name="email" required value="<%= ViewUtil.html(form.get("email")) %>">

            <label>Password</label>
            <input type="password" name="password" required>

            <button class="btn btn-primary btn-block" type="submit">Login</button>
        </form>
        <p class="form-note">New here? <a href="<%= contextPath %>/register">Create an account</a>.</p>
    </div>
</section>

<%@ include file="partials/footer.jspf" %>
