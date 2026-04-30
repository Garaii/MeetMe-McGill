/* CONTRIBUTORS AND TASK COORDINATION
IKRAM

IKRAM: CREATED THE OWNER DASHBOARD PAGE WITH ALL NECESSARY ACTIONS REQUIRED TO THE USER,
 FOLLOWING A VIEW PORT LAYOUT TO PREVENT GOING FROM PAGES AND BACK 
 ORDERING THE OWNER APPOINTMENTS TABS FOR EVERY ACTION AND OPTIMIZING FLOW
 
*/ 
// OwnerDashboard.jsx
import { useState, useEffect } from 'react'
import Navbar from '../../components/Navbar'
import {apiGet, apiPost} from '../../api'

function OwnerDashboard({ user, onLogout, initialBookingOwnerId }) {
  const [view, setView] = useState("slots")

  // Slots state
  const [slots, setSlots] = useState([])
  const [slotsLoading, setSlotsLoading] = useState(true)
 
  // Requests state
  const [requests, setRequests] = useState([])
  const [requestsLoaded, setRequestsLoaded] = useState(false)

  // Create slot form state
  const [slotDate, setSlotDate] = useState('')
  const [startTime, setStartTime] = useState('')
  const [endTime, setEndTime] = useState('')
  const [slotLocation, setSlotLocation] = useState('')
  const [slotError, setSlotError] = useState('')
  const [slotSuccess, setSlotSuccess] = useState('')
  const [slotLoading, setSlotLoading] = useState(false)

  // Create group meeting form state
  const [meetingTitle, setMeetingTitle] = useState('')
  const [meetingDescription, setMeetingDescription] = useState('')
  const [meetingLocation, setMeetingLocation] = useState('')
  const [meetingOptions, setMeetingOptions] = useState([
    { option_date: '', start_time: '', end_time: '' },
    { option_date: '', start_time: '', end_time: '' },
  ])
  const [meetingError, setMeetingError] = useState('')
  const [meetingSuccess, setMeetingSuccess] = useState('')
  const [meetingLoading, setMeetingLoading] = useState(false)

  // FOR GROUP MEETING
  const [groupMeetings, setGroupMeetings] = useState([])
  const [selectedMeeting, setSelectedMeeting] = useState(null)
  const [meetingVotes, setMeetingVotes] = useState([])
  const [groupAttendees, setGroupAttendees] = useState([])
  const [votesLoading, setVotesLoading] = useState(false)
  const [finalizeMessage, setFinalizeMessage] = useState('')
  const [finalizeError, setFinalizeError] = useState(false)

  // INVITATION URL STATE
  const [inviteUrl, setInviteUrl] = useState('')
  const [inviteCopied, setInviteCopied] = useState(false)

  // RECURRING OFFICE HOURS
  const [recurWeekday, setRecurWeekday] = useState('')
  const [recurStart, setRecurStart] = useState('')
  const [recurEnd, setRecurEnd] = useState('')
  const [recurWeeks, setRecurWeeks] = useState('')
  const [recurLocation, setRecurLocation] = useState('')
  const [recurringBatches, setRecurringBatches] = useState([])
  const [recurError, setRecurError] = useState('')
  const [recurSuccess, setRecurSuccess] = useState('')
  const [recurLoading, setRecurLoading] = useState(false)

  // Book with other owners
  const [bookOwners, setBookOwners] = useState([])
  const [bookOwnersLoading, setBookOwnersLoading] = useState(false)
  const [bookOwnersLoaded, setBookOwnersLoaded] = useState(false)
  const [selectedBookOwner, setSelectedBookOwner] = useState(null)
  const [bookOwnerSlots, setBookOwnerSlots] = useState([])
  const [bookOwnerSlotsLoading, setBookOwnerSlotsLoading] = useState(false)
  const [bookOtherMessage, setBookOtherMessage] = useState('')
  const [bookOtherError, setBookOtherError] = useState(false)

  // Request meeting with another owner
  const [otherOwnerId, setOtherOwnerId] = useState('')
  const [otherRequestDate, setOtherRequestDate] = useState('')
  const [otherRequestStart, setOtherRequestStart] = useState('')
  const [otherRequestEnd, setOtherRequestEnd] = useState('')
  const [otherRequestMessage, setOtherRequestMessage] = useState('')
  const [otherRequestError, setOtherRequestError] = useState('')
  const [otherRequestSuccess, setOtherRequestSuccess] = useState('')
  const [otherRequestLoading, setOtherRequestLoading] = useState(false)

  // ======================= Fetch slots from php on mount
  useEffect(() => {
    apiGet('owner_slots.php')
      .then(data => {
        setSlots(data.slots || [])
        setSlotsLoading(false)
      })
      .catch(() => setSlotsLoading(false))

    // creating URL using current user's ID
    // Links to owner's booking page
    setInviteUrl(`${window.location.origin}/?owner_id=${user.id}`)
  }, [])

  // ======================== Fetch requests only when tab opened
  const fetchRequests = () => {
    if (requestsLoaded) return

    apiGet('owner_requests.php')
      .then(data => {
        setRequests(data.requests || [])
        setRequestsLoaded(true)
      })
  }

  // ======================== Fetch group meetings
  const fetchGroupMeetings = () => {
    apiGet('create_group_meeting.php')
      .then(data => {
        setGroupMeetings(data.meetings || [])
      })
      .catch(() => {})
  }

  // ======================== Fetch recurring batches
  const fetchRecurringBatches = () => {
    apiGet('create_recurring_office_hours.php')
      .then(data => {
        setRecurringBatches(data.batches || [])
      })
      .catch(() => {})
  }

  // ======================== Fetch owners for booking/requesting
  const fetchBookOwners = () => {
    if (bookOwnersLoaded) return

    setBookOwnersLoading(true)

    apiGet("owners_list.php")
      .then(data => {
        setBookOwners(data.owners || [])
        setBookOwnersLoaded(true)
        setBookOwnersLoading(false)
      })
      .catch(() => setBookOwnersLoading(false))
  }

  // ___________ COPY invite URL to Clipboard
  const handleCopyInvite = () => {
    navigator.clipboard.writeText(inviteUrl).then(() => {
      setInviteCopied(true)
      setTimeout(() => setInviteCopied(false), 2000)
    })
  }

  // Toggle slot active / private
  const handleToggleVisibility = async (slot_id, current_status) => {
    try {
      await apiPost("update_slot_visibility.php", {
        slot_id,
        is_active: current_status ? 0 : 1
      })
   
      setSlots(prev =>
        prev.map(s => s.id === slot_id ? { ...s, is_active: current_status ? 0 : 1 } : s)
      )
    } catch (err) {
      alert(err.message)
    }
  }

  // _____________________________ Delete slot, transforming into json
  const handleDeleteSlot = async (slot) => {
    if (!window.confirm('Delete this slot?')) return

    try {
      const data = await apiPost("delete_slot.php", { slot_id: slot.id })

      // IF THE SLOT IS BOOKED WE NOTIFY USER
      if (data.booked_user_email) {
       const body = encodeURIComponent(
        `Hi,\n\nThis is to let you know that your booking has been cancelled by ${user.name}.\n\nPlease log in to the booking app to reserve another slot.\n\nThank you!`
      )
      const subject = encodeURIComponent('Your Booking Has Been Cancelled')
      window.location.href = `mailto:${data.booked_user_email}?subject=${subject}&body=${body}`
      }

      setSlots(prev => prev.filter(s => s.id !== slot.id))
    } catch (err) {
      alert(err.message)
    }
  }

  // Email the person who booked a slot, not full email
  const handleEmailBookedUser = (slot) => {
    if (!slot.booked_by_email) return
    const subject = encodeURIComponent('Regarding Your Booking')
    const body = encodeURIComponent(
      `Hi,\n\nThis is ${user.name} reaching out regarding your booking on ${slot.slot_date} from ${slot.start_time} to ${slot.end_time}.\n\n`
    )
    window.location.href = `mailto:${slot.booked_by_email}?subject=${subject}&body=${body}`
  }

  // Accept request again transform into json for react
  const handleAcceptRequest = async (request_id) => {
    try {
      await apiPost("accept_request.php", { request_id })

      setRequests(prev =>
        prev.filter(r => r.id !== request_id)
      )

      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) {
      alert(err.message)
    }
  }

  // Decline request
  const handleDeclineRequest = async (request_id) => {
    try {
      await apiPost("decline_request.php", { request_id })

      setRequests(prev =>
        prev.filter(r => r.id !== request_id)
      )
    } catch (err) {
      alert(err.message)
    }
  }

  // _______________________ Create single slot transforming to php -> json -> react
  const handleCreateSlot = async () => {
    setSlotError('')
    setSlotSuccess('')
    setSlotLoading(true)

    try {
      const data = await apiPost("create_slot.php", {
        slot_date: slotDate,
        start_time: startTime,
        end_time: endTime,
        location: slotLocation
      })

      setSlotSuccess(data.message)
      setSlotDate('')
      setStartTime('')
      setEndTime('')
      setSlotLocation('')

      // Refresh slots list
      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) {
      setSlotError(err.message)
    } finally {
      setSlotLoading(false)
    }
  }

  // ____________________________ create recurring OFFICE HOURS
  const handleCreateRecurring = async () => {
    setRecurError('')
    setRecurSuccess('')
    setRecurLoading(true)

    try {
      const data = await apiPost("create_recurring_office_hours.php", {
        weekday: recurWeekday,
        start_time: recurStart,
        end_time: recurEnd,
        weeks: recurWeeks,
        location: recurLocation
      })

      setRecurSuccess(data.message)
      setRecurWeekday('')
      setRecurStart('')
      setRecurEnd('')
      setRecurWeeks('')
      setRecurLocation('')

      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
      fetchRecurringBatches()
    } catch (err) {
      setRecurError(err.message)
    } finally {
      setRecurLoading(false)
    }
  }

  // ____________________________ toggle recurring batch active/private
  const handleToggleBatch = async (batch) => {
    try {
      await apiPost("create_recurring_office_hours.php", {
        action: "toggle_batch",
        batch_id: batch.id,
        is_active: batch.is_active ? 0 : 1
      })

      fetchRecurringBatches()
      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) {
      setRecurError(err.message)
    }
  }

  // ____________________________ delete recurring batch
  const handleDeleteBatch = async (batch) => {
    if (!window.confirm('Delete this recurring batch?')) return

    try {
      await apiPost("create_recurring_office_hours.php", {
        action: "delete_batch",
        batch_id: batch.id
      })

      fetchRecurringBatches()
      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
    } catch (err) {
      setRecurError(err.message)
    }
  }

  // ____________________________ select owner to book with
  const handleSelectBookOwner = (owner) => {
    setSelectedBookOwner(owner)
    setBookOwnerSlots([])
    setBookOtherMessage('')
    setBookOtherError(false)
    setBookOwnerSlotsLoading(true)
    setView("book_with_others")

    apiGet(`owner_booking_page.php?owner_id=${owner.id}`)
      .then(data => {
        setSelectedBookOwner(data.owner)
        setBookOwnerSlots(data.slots || [])
        setBookOwnerSlotsLoading(false)
      })
      .catch(err => {
        setBookOtherMessage(err.message)
        setBookOtherError(true)
        setBookOwnerSlotsLoading(false)
      })
  }

  // ____________________________ book another owner's slot
  const handleBookOtherSlot = async (slot) => {
    setBookOtherMessage('')
    setBookOtherError(false)

    try {
      const data = await apiPost("book_slot.php", { slot_id: slot.id })
      setBookOtherMessage(data.message || "Slot booked successfully.")
      setBookOtherError(false)

      if (selectedBookOwner) {
        apiGet(`owner_booking_page.php?owner_id=${selectedBookOwner.id}`)
          .then(d => setBookOwnerSlots(d.slots || []))
          .catch(() => {})
      }
    } catch (err) {
      setBookOtherMessage(err.message)
      setBookOtherError(true)
    }
  }

  // ____________________________ send meeting request to another owner
  const handleSendOtherRequest = async () => {
    setOtherRequestError('')
    setOtherRequestSuccess('')

    if (!otherOwnerId || !otherRequestDate || !otherRequestStart || !otherRequestEnd || otherRequestMessage.trim() === '') {
      setOtherRequestError("Please select an owner, suggest a time, and write a message.")
      return
    }

    if (otherRequestEnd <= otherRequestStart) {
      setOtherRequestError("End time must be later than start time.")
      return
    }

    setOtherRequestLoading(true)

    try {
      const data = await apiPost("request_meeting.php", {
        owner_id: otherOwnerId,
        requested_date: otherRequestDate,
        start_time: otherRequestStart,
        end_time: otherRequestEnd,
        message: otherRequestMessage
      })

      setOtherRequestSuccess(data.message || "Meeting request sent successfully.")
      setOtherOwnerId('')
      setOtherRequestDate('')
      setOtherRequestStart('')
      setOtherRequestEnd('')
      setOtherRequestMessage('')
    } catch (err) {
      setOtherRequestError(err.message)
    } finally {
      setOtherRequestLoading(false)
    }
  }

  // ____________________________ load booking link inside owner dashboard
  useEffect(() => {
    if (!initialBookingOwnerId) return

    fetchBookOwners()
    handleSelectBookOwner({ id: initialBookingOwnerId, name: "selected owner" })
  }, [initialBookingOwnerId])

  // __________________ Group meeting option helpers transforming to php -> json -> react
  const updateMeetingOption = (index, field, value) => {
    setMeetingOptions(prev =>
      prev.map((opt, i) => i === index ? { ...opt, [field]: value } : opt)
    )
  }

  const addMeetingOption = () => {
    setMeetingOptions(prev => [...prev, { option_date: '', start_time: '', end_time: '' }])
  }

  const removeMeetingOption = (index) => {
    setMeetingOptions(prev => prev.filter((_, i) => i !== index))
  }

  // Create group meeting transforming to php -> json -> react
  const handleCreateGroupMeeting = async () => {
    setMeetingError('')
    setMeetingSuccess('')
    setMeetingLoading(true)

    try {
      const data = await apiPost("create_group_meeting.php", {
        title: meetingTitle,
        description: meetingDescription,
        location: meetingLocation,
        options: meetingOptions
      })

      setMeetingSuccess(`Meeting created! Share ID ${data.group_meeting_id} with your students.`)

      const subject = encodeURIComponent(`Group Meeting Invitation - ${meetingTitle}`)
      const body = encodeURIComponent(
        `Hi,\n\nYou have been invited by ${user.name} to vote on a group meeting: "${meetingTitle}".\n\nGo to the booking app and click "Submit Availability", then enter Meeting ID: ${data.group_meeting_id}\n\nThank you!`
      )

      window.location.href = `mailto:?subject=${subject}&body=${body}`

      // Add to local list so owner can view votes right away
      setGroupMeetings(prev => [...prev, { id: data.group_meeting_id, title: meetingTitle, location: meetingLocation }])
           
      setMeetingTitle('')
      setMeetingDescription('')
      setMeetingLocation('')
      setMeetingOptions([
        { option_date: '', start_time: '', end_time: '' },
        { option_date: '', start_time: '', end_time: '' },
      ])
    } catch (err) {
      setMeetingError(err.message)
    } finally {
      setMeetingLoading(false)
    }
  }

  // *******************_______ View votes for a group meeting ────────────────────────────────
  const handleViewVotes = async (meeting) => {
    setSelectedMeeting(meeting)
    setMeetingVotes([])
    setGroupAttendees([])
    setFinalizeMessage('')
    setFinalizeError(false)
    setVotesLoading(true)
    setView("view_votes")

    try {
      const data = await apiGet(`view_group_counts.php?group_meeting_id=${meeting.id}`)
      setMeetingVotes(data.options || [])
      setGroupAttendees(data.attendees || [])
    } catch (err) {
      setFinalizeMessage(err.message)
      setFinalizeError(true)
    } finally {
      setVotesLoading(false)
    }
  }
 
  // ***************__________________ Finalize a group meeting option ───────────────────────────────
  const handleFinalizeOption = async (option_id) => {
    if (!window.confirm('Finalize this time slot? It will be created as an active booking slot.')) return

    setFinalizeMessage('')
    setFinalizeError(false)

    try {
      const data = await apiPost("finalize_group_meeting.php", { option_id })
      setFinalizeMessage(data.message)
      setFinalizeError(false)

      apiGet("owner_slots.php").then(d => setSlots(d.slots || []))
      fetchGroupMeetings()

      if (selectedMeeting) {
        apiGet(`view_group_counts.php?group_meeting_id=${selectedMeeting.id}`)
          .then(d => {
            setMeetingVotes(d.options || [])
            setGroupAttendees(d.attendees || [])
          })
          .catch(() => {})
      }
     
    } catch (err) {
      setFinalizeMessage(err.message)
      setFinalizeError(true)
    }
  }
  /* ++++++++++++++++++----------------GROUPING SLOTS ---------------+++++++++++++ */
  const groupedSlots = {
  recurring: slots.filter(s => s.title === 'Recurring office hours'),
  requested: slots.filter(s => s.title === 'Meeting request'),
  available: slots.filter(s => s.title === 'Available slot'),
  group: slots.filter(s => !['Recurring office hours', 'Meeting request', 'Available slot'].includes(s.title))
  }

  //  ####################################%%%%%%%%%%%%%%%%%%%_____________________ actual UI
  return (
    <div>
      <Navbar onLogout={onLogout} user={user} />
      <div className="dashboard">
        <h1>Welcome back, {user.name}!</h1>

        {/* ___________ FOR INVITATION ___________ */}
        <div className="invite-banner">
          <span>Your booking link:</span>
          <code>{inviteUrl}</code>
          <button onClick={handleCopyInvite}>{inviteCopied ? "Copied!" : "Copy Link"}</button>
        </div>

        {/* TABS */}
        <div className="dashboard-tabs">
          <button className="appt-tab-btn" onClick={() => setView("slots")}>
            My Slots
          </button>

          <button className="appt-tab-btn" onClick={() => { setView("book_with_others"); fetchBookOwners() }}>
            Book With Others
          </button>

          <button className="appt-tab-btn" onClick={() => { setView("requests"); fetchRequests() }}>
            Meeting Requests
          </button>

          <button className="appt-tab-btn" onClick={() => setView("create_slot")}>
            Create Slot
          </button>

          <button className="appt-tab-btn" onClick={() => setView("create_group")}>
            Create Group Meeting
          </button>

          <button className="appt-tab-btn" onClick={() => { setView("group_meetings"); fetchGroupMeetings() }}>
            View Group Votes
          </button>

          <button className="appt-tab-btn" onClick={() => { setView("recurring"); fetchRecurringBatches() }}>
            Recurring Office Hours
          </button>
        </div>

        {/* == MY SLOTS == */}
        {view === "slots" && (
          <section className="appointments-view">
            <h2>My Slots</h2>
            {slotsLoading ? ( <p>Loading...</p> ) : slots.length === 0 ? ( <p>You have no slots yet.</p> ) : (
              <>

              {/* _____ MANUAL / RECURRING SLOTS ______ */}
              {groupedSlots.available.length > 0 && (
              <>
                <h3 style={{ marginTop: '16px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
                  Single Slots
                </h3>
                <table className="dashboard-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Start</th>
                      <th>End</th>
                      <th>Location</th>
                      <th>Status</th>
                      <th>Booked By</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {groupedSlots.available.map(slot => (
                      <tr key={slot.id}>
                        <td>{slot.slot_date}</td>
                        <td>{slot.start_time}</td>
                        <td>{slot.end_time}</td>
                        <td>{slot.location || "Not specified"}</td>
                        <td>{slot.is_active ? "Active" : "Private"}</td>
                        <td>{slot.booked_by ?? "Not booked"}</td>
                        <td>
                          <div className='action-btn'>
                          <button className='activity-btn' onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                            {slot.is_active ? "Make Private" : "Activate"}
                          </button>
                          {slot.booked_by && (
                            <button className='email-btn' onClick={() => handleEmailBookedUser(slot)}>Email User</button>
                          )}
                          <button className='delete-btn' onClick={() => handleDeleteSlot(slot)}>
                            Delete
                          </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
                </>
              )}

              {/* ___________ GROUP MEETING SLOTS ____________ */}
              {groupedSlots.group.length > 0 && (
                <>
                  <h3 style={{ marginTop: '24px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
                    Group Meeting Slots
                  </h3>
                  <table className="dashboard-table">
                    <thead>
                      <tr>
                        <th>Title</th><th>Date</th><th>Start</th><th>End</th><th>Location</th>
                        <th>Status</th><th>Booked By</th><th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {groupedSlots.group.map(slot => (
                        <tr key={slot.id}>
                          <td>{slot.title}</td>
                          <td>{slot.slot_date}</td>
                          <td>{slot.start_time}</td>
                          <td>{slot.end_time}</td>
                          <td>{slot.location || "Not specified"}</td>
                          <td>{slot.is_active ? "Active" : "Private"}</td>
                          <td>{slot.booked_by ?? "Not booked"}</td>
                          <td>
                            <div className='action-btn'>
                            <button className='activity-btn' onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                              {slot.is_active ? "Make Private" : "Activate"}
                            </button>
                            {slot.booked_by && (
                              <button className='email-btn' onClick={() => handleEmailBookedUser(slot)}>Email User</button>
                            )}
                            <button className='delete-btn' onClick={() => handleDeleteSlot(slot)}>Delete</button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </>
              )}

                {/* __________________ request _________________ */}
               {groupedSlots.requested.length > 0 && (
                <>
                  <h3 style={{ marginTop: '24px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
                    Requested Meeting Slots
                  </h3>
                  <table className="dashboard-table">
                    <thead>
                      <tr>
                        <th>Title</th><th>Date</th><th>Start</th><th>End</th><th>Location</th>
                        <th>Status</th><th>Booked By</th><th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {groupedSlots.requested.map(slot => (
                        <tr key={slot.id}>
                          <td>{slot.title}</td>
                          <td>{slot.slot_date}</td>
                          <td>{slot.start_time}</td>
                          <td>{slot.end_time}</td>
                          <td>{slot.location || "Not specified"}</td>
                          <td>{slot.is_active ? "Active" : "Private"}</td>
                          <td>{slot.booked_by ?? "Not booked"}</td>
                          <td>
                            <div className='action-btn'>
                            <button className='activity-btn' onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                              {slot.is_active ? "Make Private" : "Activate"}
                            </button>
                            {slot.booked_by && (
                              <button className='email-btn' onClick={() => handleEmailBookedUser(slot)}>Email User</button>
                            )}
                            <button className='delete-btn' onClick={() => handleDeleteSlot(slot)}>Delete</button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </>
              )}



              {/* ________________recurring_______________ */}
               {groupedSlots.recurring.length > 0 && (
                <>
                  <h3 style={{ marginTop: '40px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
                    Recurring Meeting Slots
                  </h3>
                  <table className="dashboard-table">
                    <thead>
                      <tr>
                        <th>Title</th><th>Date</th><th>Start</th><th>End</th><th>Location</th>
                        <th>Status</th><th>Booked By</th><th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {groupedSlots.recurring.map(slot => (
                        <tr key={slot.id}>
                          <td>{slot.title}</td>
                          <td>{slot.slot_date}</td>
                          <td>{slot.start_time}</td>
                          <td>{slot.end_time}</td>
                          <td>{slot.location || "Not specified"}</td>
                          <td>{slot.is_active ? "Active" : "Private"}</td>
                          <td>{slot.booked_by ?? "Not booked"}</td>
                          <td>
                            <div className='action-btn'>
                            <button className='activity-btn' onClick={() => handleToggleVisibility(slot.id, slot.is_active)}>
                              {slot.is_active ? "Make Private" : "Activate"}
                            </button>
                            {slot.booked_by && (
                              <button className='email-btn' onClick={() => handleEmailBookedUser(slot)}>Email User</button>
                            )}
                            <button className='delete-btn' onClick={() => handleDeleteSlot(slot)}>Delete</button>
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </>
              )}
            </>
          )}
          </section>
        )}

        {/* ── BOOK WITH OTHERS ── */}
        {view === "book_with_others" && (
          <section className="browse-view">
            <h2>Book With Others</h2>

            <h3 style={{ marginTop: '8px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
              Browse Owners
            </h3>

            {bookOwnersLoading && <p>Loading owners...</p>}

            {!bookOwnersLoading && bookOwners.length === 0 && (
              <p>No other owners found.</p>
            )}

            {!bookOwnersLoading && bookOwners.length > 0 && (
              <div className="owners-list">
                {bookOwners.map(owner => (
                  <div key={owner.id} className="owner-card">
                    <div>
                      <strong>{owner.name}</strong>
                      <span>{owner.email}</span>
                    </div>
                    <button className="btn-primary" onClick={() => handleSelectBookOwner(owner)}>
                      View Slots
                    </button>
                  </div>
                ))}
              </div>
            )}

            {bookOtherMessage && (
              <p className={bookOtherError ? "auth-error" : "form-message"}>{bookOtherMessage}</p>
            )}

            {selectedBookOwner && (
              <div style={{ marginTop: '18px' }}>
                <button onClick={() => { setSelectedBookOwner(null); setBookOwnerSlots([]); setBookOtherMessage('') }}>
                  ← Back to owners
                </button>
                <h3 style={{ marginTop: '14px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
                  Available slots with {selectedBookOwner.name}
                </h3>

                {bookOwnerSlotsLoading && <p>Loading slots...</p>}

                {!bookOwnerSlotsLoading && bookOwnerSlots.length === 0 && (
                  <p>No available slots for this person.</p>
                )}

                {!bookOwnerSlotsLoading && bookOwnerSlots.length > 0 && (
                  <table className="dashboard-table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Location</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      {bookOwnerSlots.map(slot => (
                        <tr key={slot.id}>
                          <td>{slot.slot_date}</td>
                          <td>{slot.start_time}</td>
                          <td>{slot.end_time}</td>
                          <td>{slot.location || "Not specified"}</td>
                          <td>
                            <button className="btn-primary" onClick={() => handleBookOtherSlot(slot)}>
                              Book
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                )}
              </div>
            )}

            <h3 style={{ marginTop: '28px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
              Request Meeting
            </h3>

            {otherRequestError && <p className="auth-error">{otherRequestError}</p>}
            {otherRequestSuccess && <p className="auth-success">{otherRequestSuccess}</p>}

            <div className="auth-form">
              <div className="form-group">
                <label>Select Owner</label>
                <select value={otherOwnerId} onChange={e => setOtherOwnerId(e.target.value)}>
                  <option value="">-- Choose an owner --</option>
                  {bookOwners.map(owner => (
                    <option key={owner.id} value={owner.id}>
                      {owner.name} ({owner.email})
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Suggested Date</label>
                <input type="date" value={otherRequestDate} onChange={e => setOtherRequestDate(e.target.value)} />
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Start Time</label>
                  <input type="time" value={otherRequestStart} onChange={e => setOtherRequestStart(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>End Time</label>
                  <input type="time" value={otherRequestEnd} onChange={e => setOtherRequestEnd(e.target.value)} />
                </div>
              </div>

              <div className="form-group">
                <label>Message</label>
                <textarea
                  placeholder="Describe why you'd like to meet..."
                  value={otherRequestMessage}
                  onChange={e => setOtherRequestMessage(e.target.value)}
                  rows={4}
                />
              </div>

              <button className="btn-primary btn-full" onClick={handleSendOtherRequest} disabled={otherRequestLoading}>
                {otherRequestLoading ? "Sending..." : "Send Request"}
              </button>
            </div>
          </section>
        )}

        {/* ── MEETING REQUESTS ── */}
        {view === "requests" && (
          <section className="browse-view">
            <h2>Meeting Requests</h2>
            {!requestsLoaded ? (
              <p>Loading...</p>
            ) : requests.length === 0 ? (
              <p>No meeting requests.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Suggested Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {requests.map(req => (
                    <tr key={req.id}>
                      <td>{req.requester_name}</td>
                      <td>
                        <a href={`mailto:${req.requester_email}`}>{req.requester_email}</a>
                      </td>
                      <td>{req.title}</td>
                      <td>{req.message}</td>
                      <td>{req.requested_date}</td>
                      <td>{req.requested_start}</td>
                      <td>{req.requested_end}</td>
                      <td>
                        <div className='action-btn'>
                        <button className='accept-btn' onClick={() => handleAcceptRequest(req.id)}>Accept</button>
                        <button className='delete-btn' onClick={() => handleDeclineRequest(req.id)}>Decline</button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* ── CREATE SLOT ── */}
        {view === "create_slot" && (
          <div className="auth-page">
            <section className="auth-card recurring-card">
              <h2>Create a Slot</h2>

              {slotError && <p className="auth-error">{slotError}</p>}
              {slotSuccess && <p className="auth-success">{slotSuccess}</p>}

              <div className="auth-form">
                <div className="form-group">
                  <label>Date</label>
                  <input type="date" value={slotDate} onChange={e => setSlotDate(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>Start Time</label>
                  <input type="time" value={startTime} onChange={e => setStartTime(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>End Time</label>
                  <input type="time" value={endTime} onChange={e => setEndTime(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>Location (optional)</label>
                  <input type="text" value={slotLocation} onChange={e => setSlotLocation(e.target.value)} />
                </div>

                <button className="btn-primary btn-full" onClick={handleCreateSlot} disabled={slotLoading}>
                  {slotLoading ? 'Creating...' : 'Create Slot'}
                </button>
              </div>
            </section>
          </div>
        )}

        {/* **********************______________________RECCURING OFFICE HOURS _________________******************* */}
        {view === "recurring" && (
          <div className="auth-page recurring-page">
            <section className="auth-card recurring-batch-card">
              <h2>Recurring Office Hours</h2>

              <p className="auth-subtitle">Create the same slot every week for X weeks</p>

              {recurError && <p className="auth-error">{recurError}</p>}
              {recurSuccess && <p className="auth-success">{recurSuccess}</p>}

              <div className="auth-form">
                <div className="form-group">
                  <label>Weekday</label>
                  <select value={recurWeekday} onChange={e => setRecurWeekday(e.target.value)}>
                    <option value="">-- Select a weekday --</option>
                    {["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"].map(d => (
                      <option key={d} value={d}>{d}</option>
                    ))}
                  </select>
                </div>

                <div className="form-group">
                  <label>Start Time</label>
                  <input type="time" value={recurStart} onChange={e => setRecurStart(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>End Time</label>
                  <input type="time" value={recurEnd} onChange={e => setRecurEnd(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>Number of Weeks</label>
                  <input type="number" min="1" value={recurWeeks} onChange={e => setRecurWeeks(e.target.value)} />
                </div>

                <div className="form-group">
                  <label>Location (optional)</label>
                  <input type="text" value={recurLocation} onChange={e => setRecurLocation(e.target.value)} />
                </div>

                <button className="btn-primary btn-full" onClick={handleCreateRecurring} disabled={recurLoading}>
                  {recurLoading ? 'Creating...' : 'Create Recurring Slots'}
                </button>
              </div>

            </section>

            <section className="auth-card recurring-batch-card">
              <div className="recurring-batches">
                <h2>
                  Recurring Batches
                </h2>

                {recurringBatches.length === 0 ? (
                  <p>No recurring batches yet.</p>
                ) : (
                  <div className="recurring-table-wrap">
                    <table className="dashboard-table">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Weeks</th>
                          <th>Location</th>
                          <th>Slots</th>
                          <th>Booked</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {recurringBatches.map(batch => (
                          <tr key={batch.id}>
                            <td>{batch.title}</td>
                            <td>{batch.weeks}</td>
                            <td>{batch.location || "Not specified"}</td>
                            <td>{batch.slot_count}</td>
                            <td>{batch.booked_count}</td>
                            <td>{batch.is_active ? "Active" : "Private"}</td>
                            <td>
                              <div className='action-btn'>
                              <button className='activity-btn' onClick={() => handleToggleBatch(batch)}>
                                {batch.is_active ? "Make Private" : "Activate"}
                              </button>
                              <button className="delete-btn" onClick={() => handleDeleteBatch(batch)}>
                                Delete Batch
                              </button>
                              </div>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </section>
          </div>
        )}

        {/* ################## CREATE GROUP MEETING ################### */}
        {view === "create_group" && (
          <div className='auth-page'>
            <section className="auth-card">
              <h2 className='title-meet'>Create Group Meeting</h2>

              {meetingError && <p className="auth-error">{meetingError}</p>}
              {meetingSuccess && <p className="auth-success">{meetingSuccess}</p>}

              <div className="auth-form">
                <div className="form-group">
                  <label>Meeting Title</label>
                  <input
                    type="text"
                    placeholder="e.g. Office Hours Week 3"
                    value={meetingTitle}
                    onChange={e => setMeetingTitle(e.target.value)}
                  />
                </div>

                <div className="form-group">
                  <label>Description (optional)</label>
                  <textarea
                    placeholder="Any details for attendees..."
                    value={meetingDescription}
                    onChange={e => setMeetingDescription(e.target.value)}
                    rows={3}
                  />
                </div>

                <div className="form-group">
                  <label>Location (optional)</label>
                  <input
                    type="text"
                    placeholder="e.g. McConnell 320"
                    value={meetingLocation}
                    onChange={e => setMeetingLocation(e.target.value)}
                  />
                </div>

                <h3>Meeting Options</h3>

                {meetingOptions.map((opt, i) => (
                  <div key={i} className="form-group meeting-option-row">
                    <label>Option {i + 1}</label>
                    <input type="date" value={opt.option_date} onChange={e => updateMeetingOption(i, 'option_date', e.target.value)} />
                    <input type="time" value={opt.start_time} onChange={e => updateMeetingOption(i, 'start_time', e.target.value)} />
                    <input type="time" value={opt.end_time} onChange={e => updateMeetingOption(i, 'end_time', e.target.value)} />

                    {meetingOptions.length > 1 && (
                      <button onClick={() => removeMeetingOption(i)}>Remove</button>
                    )}
                  </div>
                ))}

                <button onClick={addMeetingOption}>+ Add Option</button>

                <button
                  className="btn-primary btn-full"
                  onClick={handleCreateGroupMeeting}
                  disabled={meetingLoading}
                >
                  {meetingLoading ? 'Creating...' : 'Create Group Meeting'}
                </button>
              </div>
            </section>
          </div>
        )}

        {/******************______________VIEW NUMBER OF VOTES GROUP MEETING_____________********************* */}
        {view === "group_meetings" && (
          <section className="appointments-view">
            <h2>My Group Meetings</h2>

            {groupMeetings.length === 0 ? (
              <p>No group meetings yet. Create one first.</p>
            ) : (
              <div className="owners-list">
                {groupMeetings.map(meeting => (
                  <div key={meeting.id} className="owner-card">
                    <strong>{meeting.title}</strong>
                    <span>{meeting.location || "Not specified"}</span>
                    <button className="btn-primary" onClick={() => handleViewVotes(meeting)}>
                      View Votes
                    </button>
                  </div>
                ))}
              </div>
            )}
          </section>
        )}
 
        {/* **************_____________VOTE COUNTS + FINALIZE________________***************** */}
        {view === "view_votes" && selectedMeeting && (
          <section className="appointments-view">
            <button onClick={() => { setView("group_meetings")}}>← Back</button>
            <h2 style={{ marginTop: "16px" }}>
              Votes for: {selectedMeeting.title}
            </h2>
            <p className="meeting-location">
              Location: {selectedMeeting.location || "Not specified"}
            </p>

            {finalizeMessage && <p className={finalizeError ? "auth-error" : "form-message"}>{finalizeMessage}</p>}

            {votesLoading ? (
              <p>Loading votes...</p>
            ) : meetingVotes.length === 0 ? (
              <p>No votes yet.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Votes</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>
                  {meetingVotes.map(option => (
                    <tr key={option.id}>
                      <td>{option.option_date}</td>
                      <td>{option.start_time}</td>
                      <td>{option.end_time}</td>
                      <td>{option.vote_count}</td>
                      <td>
                        <button className="btn-primary" onClick={() => handleFinalizeOption(option.id)}>
                          Finalize
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}

            <h3 style={{ marginTop: '24px', marginBottom: '8px', fontSize: '15px', color: 'var(--text-muted)' }}>
              Final Attendees
            </h3>

            {groupAttendees.length === 0 ? (
              <p>No finalized attendees yet.</p>
            ) : (
              <table className="dashboard-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody>
                  {groupAttendees.map(attendee => (
                    <tr key={attendee.email}>
                      <td>{attendee.name}</td>
                      <td>{attendee.email}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </section>
        )}

        {/* BOTTOM ACTIONS  $$$$$$$$$$$$$$$$$$$$$$$$$$ maybe put this button elsewhere $$$$$$$$$$$$$$$$$$$$$$$$$$$$ */}
        {/*Footer*/}
        <footer className="footer">
            <p>© 2026 MeetMe@McGill — SOCS Booking App</p>
        </footer>
       
      </div>
    </div>
  )
}

export default OwnerDashboard
