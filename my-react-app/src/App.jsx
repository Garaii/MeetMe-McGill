import { useState, useEffect } from "react"
import './App.css'
import LandingPage from './pages/Landing'
import LoginPage from './pages/Login'
import DashboardPage from './pages/Dashboard'
import RegisterPage from './pages/Register'
import BookPage from'./pages/BookPage'
import CreeatSlotPage from './owner/CreateSlot'
import OwnerDashboard from './owner/OwnerDashboard'
import SlotDetail from './owner/SlotDetail'



function App() {
  const [page, setPage] = useState("landing")
  const[user, setUser] = useState(null)
  
  //first just handling is the user is an owner or student
  function handleLogin(userData) {
    setUser(userData)
    // redirect based on role after login
    if (userData.role === "owner") {
      setPage("ownerDashboard")
    } else {
      setPage("dashboard")
    }
  }

  function handleLogout() {
    setUser(null)
    setPage("landing")
  }
  
  return (
    <>
    <div className="app_shell">
      {page === "landing"        && <LandingPage        onLogin={() => setPage("login")} onRegister={() => setPage("register")} />}
      {page === "login"          && <LoginPage          onLogin={handleLogin} onGoRegister={() => setPage("register")} />}
      {page === "register"       && <RegisterPage       onRegistered={handleLogin} onGoLogin={() => setPage("login")} />}
      {page === "dashboard"      && <DashboardPage      user={user} onLogout={handleLogout} onBook={() => setPage("book")} />}
      {page === "book"           && <BookPage           user={user} onBack={() => setPage("dashboard")} />}
      {page === "ownerDashboard" && <OwnerDashboard     user={user} onLogout={handleLogout} onCreateSlot={() => setPage("createSlot")} onViewSlot={() => setPage("slotDetail")} />}
      {page === "createSlot"     && <CreateSlotPage     user={user} onBack={() => setPage("ownerDashboard")} />}
      {page === "slotDetail"     && <SlotDetail         user={user} onBack={() => setPage("ownerDashboard")} />}
    </div>
     
    </>
  )
}

export default App
