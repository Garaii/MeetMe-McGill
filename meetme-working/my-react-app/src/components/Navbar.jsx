// navbar.jsx
function Navbar({ onLogin, onRegister }) {
  return (
    <nav className="navbar">
      <span className="nav-logo">Meet Me @ McGill</span>
      <div className="nav-links">
        <button onClick={onLogin}>Login</button>
        <button onClick={onRegister} className="btn-primary">Register</button>
      </div>
    </nav>
  )
}
export default Navbar