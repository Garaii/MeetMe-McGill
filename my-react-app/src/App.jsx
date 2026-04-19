import { useState, useEffect } from "react"
import './App.css'
import LandingPage from './pages/Landing'
import LoginPage from './pages/Login'
import DashboardPage from './pages/Dashboard'
import RegisterPage from './pages/Register'
import BookPage from'./pages/BookPage'
import CreateSlotPage from './pages/owner/CreateSlot'
import OwnerDashboard from './pages/owner/OwnerDashboard'
import SlotDetail from './pages/owner/SlotDetail'

function handleLogin(email, password){
  setUser(userData)
  if(userData.role === "owner"){
    setPage("ownerDashboard")
  }else{
    setPage("")
  }
}

function App() {
  {/* HARD CODED USER TO I CAN VISUALIZE THE DASHBOARD */}
  const [user, setUser] = useState({ 
    name: "Kevin", 
    email: "tame.impala@mail.mcgill.ca", 
    role: "student" 
  })

  {/*}
  const [page, setPage] = useState("landing")
  */}
  {/*just to visualise dashboard for changes then uncomment the line above to restore*/}
  const [page, setPage] = useState("dashboard")

  const[user, setUser] = useState(null)
  
  //first just handling is the user is an owner or student
  function handleLogin(userData) {
    {/* 
      will fetch call to "login.php" or whatever you called it
      fetch("serever/api/login.php")
      than using json?? will tranfer from php to react app
      below is just example code not actually the real code
      */}

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
