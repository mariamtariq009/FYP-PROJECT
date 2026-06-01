from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)

CORS(app)

# =========================
# CHATBOT API
# =========================

@app.route("/chat", methods=["POST"])

def chat():

    user_message = request.json["message"].lower()

    # =========================
    # GREETINGS
    # =========================

    greetings = [

        "hi",
        "hello",
        "hey",
        "salam",
        "aoa",
        "assalamualaikum"

    ]

    if any(word in user_message for word in greetings):

        return jsonify({

            "reply":
            "Hello 👋 I am your AI Shuttle Assistant. Ask me about shuttle schedule, fares or booking."

        })

    # =========================
    # BOOKING RELATED
    # =========================

    booking_words = [

        "booking",
        "book",
        "book bus",
        "reserve",
        "reservation",
        "apply",
        "form",
        "seat",
        "ticket",
        "ride",
        "mujhe bus book karni h",
        "booking kese kare",
        "bus reserve karni h",
        "how to book a bus",
        "how can i book a bus",
        "booking process"

    ]

    if any(word in user_message for word in booking_words):

        return jsonify({

            "reply":
            "To book a bus, fill out the booking form with your Name, Email, Route, Date and Timing. After admin approval you will receive a confirmation email."

        })

    # =========================
    # MAIN TO PARAS
    # =========================

    main_to_paras_words = [

        "main to paras",
        "main campus to paras",
        "paras jana",
        "paras bus",
        "paras timing",
        "paras route",
        "paras shift",
        "paras kab jaye gi",
        "main sy paras"

    ]

    if any(word in user_message for word in main_to_paras_words):

        return jsonify({

            "reply":
            "Main Campus to PARAS buses are available at 7:15 AM and 8:00 AM."

        })

    # =========================
    # PARAS TO MAIN
    # =========================

    paras_to_main_words = [

        "paras to main",
        "paras sy main",
        "paras se main",
        "main campus",
        "return bus",
        "paras return",
        "paras sy bus",
        "paras ka return timing"

    ]

    if any(word in user_message for word in paras_to_main_words):

        return jsonify({

            "reply":
            "PARAS to Main Campus buses are available at 2:00 PM and 4:30 PM."

        })

    # =========================
    # SHUTTLE #01
    # =========================

    shuttle1_words = [

        "shuttle 1",
        "shuttle #01",
        "bus 1",
        "route 1"

    ]

    if any(word in user_message for word in shuttle1_words):

        return jsonify({

            "reply":
            "Shuttle #01 departs at 7:45 AM and arrives at 8:25 AM. Route: Main Gate → Kehkashan Hall → CS Department → GP Gate → Bank Road → Main Market → NifSat → Clock Tower → Main Gate."

        })

    # =========================
    # SHUTTLE #02
    # =========================

    shuttle2_words = [

        "shuttle 2",
        "shuttle #02",
        "bus 2",
        "route 2"

    ]

    if any(word in user_message for word in shuttle2_words):

        return jsonify({

            "reply":
            "Shuttle #02 departs at 8:35 AM and arrives at 9:15 AM."

        })

    # =========================
    # PROJECT VEHICLE FARES
    # =========================

    project_words = [

        "project fare",
        "project vehicle",
        "project bus",
        "project car",
        "project hiace"

    ]

    if any(word in user_message for word in project_words):

        return jsonify({

            "reply":
            "Project Vehicle Fares:\n🚗 Car = Rs.2500 × 12km\n🚐 Hiace = Rs.4500 × 15km\n🚌 30 Seater = Rs.6000 × 20km\n🚌 50/60 Seater = Rs.8000 × 30km"

        })

    # =========================
    # PRIVATE VEHICLE FARES
    # =========================

    private_words = [

        "private fare",
        "private vehicle",
        "private bus",
        "private car",
        "private hiace"

    ]

    if any(word in user_message for word in private_words):

        return jsonify({

            "reply":
            "Private Vehicle Fares:\n🚗 Car = Rs.4000 × 15km\n🚐 Hiace = Rs.6000 × 20km\n🚌 30 Seater = Rs.8000 × 30km\n🚌 50/60 Seater = Rs.10000 × 40km"

        })

    # =========================
    # STUDY TOUR FARES
    # =========================

    study_words = [

        "study tour",
        "tour fare",
        "study vehicle",
        "tour bus",
        "tour hiace"

    ]

    if any(word in user_message for word in study_words):

        return jsonify({

            "reply":
            "Study Tour Vehicle Fares:\n🚗 Car = Rs.1000 × 5km\n🚐 Hiace = Rs.2000 × 7km\n🚌 30 Seater = Rs.2500 × 7km\n🚌 50/60 Seater = Rs.3000 × 10km"

        })

    # =========================
    # CAR FARE
    # =========================

    if "car fare" in user_message:

        return jsonify({

            "reply":
            "🚗 Car Fare:\nProject = Rs.2500 × 12km\nPrivate = Rs.4000 × 15km\nStudy Tour = Rs.1000 × 5km"

        })

    # =========================
    # HIACE FARE
    # =========================

    if "hiace" in user_message or "apv" in user_message:

        return jsonify({

            "reply":
            "🚐 Hiace/APV Fare:\nProject = Rs.4500 × 15km\nPrivate = Rs.6000 × 20km\nStudy Tour = Rs.2000 × 7km"

        })

    # =========================
    # BUS FARE
    # =========================

    if "bus fare" in user_message:

        return jsonify({

            "reply":
            "🚌 Bus Fare:\n30 Seater = Rs.6000 × 20km\n50/60 Seater = Rs.8000 × 30km"

        })

    # =========================
    # DEFAULT RESPONSE
    # =========================

    return jsonify({

        "reply":
        "Sorry 😔 I could not understand your question. Please ask about shuttle timings, fares or booking information."

    })


# =========================
# RUN APP
# =========================

if __name__ == "__main__":

    app.run(debug=True)