import Navbar from '../components/Navbar'
function LandingPage({onLogin, onRegister}){
    return(
       <div className="landing">

      <Navbar onLogin={onLogin} onRegister={onRegister} />
       
           

        {/*Description */}
        <section className = "app_description">
           <h1>Schedule Meetings for Students & Professors</h1>
           <p>No more back-and-forth emails. Find a slot and book it in seconds.</p>
           <div className="landing_buttons">
                <button onClick={onRegister} className="btn-primary">Get Started</button>
                <button onClick={onLogin}>I already have an account</button>
            </div>
        </section>
        {/*INSTRUCTIONS - HOW It WORKS */}
                                            {/*CAN MAKE IT LIKE HERO CARDS */}
        <section className="how-it-works">
            <h2>How It Works in 3 Steps</h2>
            <div className='card-row'>
                <div className='cards'>
                    <span className='card-icon'>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path d="M7.5 6V1m10 5V1m4 16v4.5h-18v-3m17.863 -10H3.352M0.5 18.25v0.25h17.9l0.15 -0.25 0.234 -0.491A28 28 0 0 0 21.5 5.729V3.5h-18v2.128A28 28 0 0 1 0.743 17.744L0.5 18.25Z" />
                        </svg>
                    </span>
                    <h3 className="adjust-title">Set Availability</h3>
                    <p>Create time slots for meetings and office hours.</p>
                </div>

                <div className="cards">
                    <span className="card-icon search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" strokeWidth="1.7" />
                            <path d="M20 20L17 17" stroke="currentColor" strokeWidth="1.7" />
                        </svg>
                    </span>
                    <h3>Browse and Book</h3>
                    <p>Find a professor or TA and reserve an available time slot.</p>
                </div>

                <div className="cards">
                    <span className="card-icon confirm-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="1.7" />
                        <path d="M8 12l3 3 5-6" stroke="currentColor" strokeWidth="1.7" />
                        </svg>
                    </span>
                    <h3>Confirmed and Scheduled</h3>
                    <p>Your meeting is confirmed and added to your dashboard.</p>
                </div>
            </div>
        </section>
   
        {/*Footer*/}
        <footer className="footer">
            <p>© 2026 MeetMe@McGill — SOCS Booking App</p>
        </footer>
        </div>
     

    )

}
export default LandingPage