import Navbar from '../components/Navbar'

// Dashboard.jsx
function DashboardPage({user, onLogout, onBook}) {
  return (
  <div>
    <Navbar onLogout={onLogout} user={user} />
    <div className="dashboard">
    <h1>Welcome back, {user.name}! </h1>
    {/*LISTING TO SEE @MCGILL.CA WHO HAVE ACTIVE SLOTS
    * SELECT OWNER THEN SEE THEIR AVAILABLE SLOTS
    * AS A USER YOU CAN BOOK A SLOT
    * 
    * SEE THE SLOTS YOU HAVE BOOKED
    * -DELETE --> SLOT OWNER RECEIVE NOTIFICATION ':mailto'
    * -> slot goes back to available
    * - MESSAGE THE OWNER OF THE SLOT
    * 
    * Users see a dashboard listing their appointments and providing the following options:
    ▪ They can email (mailto:) the owner of a slot (selected or open slot)
    ▪ They can delete appointments.
    ▪ They can book additional appointments.
    ▪ They can logout
    * 
    * 
    */}
    {/*MY APPOINTMENTS*/}
    <section className="Appointments">
      <h2>My Appointments</h2>
      {/* appointment block with mailto owner of slot
          Delete

          THIS IS FOR EXISTING APPOINTMENTS
      */}
    </section>
    {/* APPOINTMENT ACTIONS*/}
    <div className="dashboard-actions">
      <button onClick={onBook} className="btn-primary">BOOK AN APPOINTMENT</button>
      <button onClick={onLogout}>Logout</button>
    </div>
    </div>
  </div>
    
  )
}
export default DashboardPage