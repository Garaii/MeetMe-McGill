// Navbar.jsx
function Navbar({ user, onLogin, onRegister, onLogout }) {
  return (
    <nav className="navbar">
      <span className="nav-logo">Meet Me @ McGill</span>

      <div className="nav-links">
        {user && <span className="nav-user">{user.name}</span>}

        {onLogin && <button onClick={onLogin}>Login</button>}

        {onRegister && (
          <button onClick={onRegister} className="btn-primary">
            Register
          </button>
        )}

        {onLogout && <button onClick={onLogout}>Logout</button>}
      </div>
    </nav>
  )
}

export default Navbar