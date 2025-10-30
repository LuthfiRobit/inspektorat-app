  <style>
      /* CSS untuk animasi wave dots */
      .dots-container {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 8px;
      }

      .dot {
          width: 12px;
          height: 12px;
          border-radius: 50%;
          animation: wave 1.4s infinite ease-in-out;
      }

      .dot:nth-child(1) {
          animation-delay: 0s;
      }

      .dot:nth-child(2) {
          animation-delay: 0.2s;
      }

      .dot:nth-child(3) {
          animation-delay: 0.4s;
      }

      .dot:nth-child(4) {
          animation-delay: 0.6s;
      }

      @keyframes wave {

          0%,
          100% {
              transform: translateY(0);
          }

          50% {
              transform: translateY(-15px);
              /* Tinggi gelombang */
          }
      }

      /* Menghilangkan background dan shadow popup */
      .loading-alert {
          background: transparent !important;
          box-shadow: none !important;
      }
  </style>
