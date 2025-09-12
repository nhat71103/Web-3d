import React from 'react';

const AboutPage = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      {/* Hero Section */}
      <div className="relative overflow-hidden">
        {/* Background Effects */}
        <div className="absolute inset-0 bg-gradient-to-br from-purple-600/10 via-blue-600/10 to-indigo-600/10"></div>
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-purple-500/20 via-transparent to-transparent"></div>
        
        <div className="relative max-w-7xl mx-auto px-4 py-20 sm:px-6 lg:px-8">
          <div className="text-center text-white">
            {/* Floating Animation Icon */}
            <div className="relative mb-8">
              <div className="text-8xl mb-4 animate-bounce">🌍</div>
              <div className="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-purple-400/20 rounded-full blur-3xl animate-pulse"></div>
            </div>
            
            <h2 className="text-6xl font-bold mb-6 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent animate-fade-in-up">
              Về Dự Án
            </h2>
            
            <p className="text-2xl text-white/90 mb-6 font-medium animate-fade-in-up-delay-1">
              Thế Giới Động Vật 3D - Nơi công nghệ gặp gỡ thiên nhiên
            </p>
            
            <p className="text-lg text-white/70 max-w-3xl mx-auto leading-relaxed animate-fade-in-up-delay-2">
              Chúng tôi cam kết bảo vệ sự đa dạng sinh học thông qua công nghệ tiên tiến, 
              tạo ra trải nghiệm học tập tương tác và hấp dẫn cho mọi người.
            </p>
          </div>
        </div>
      </div>

      {/* Mission & Vision */}
      <div className="max-w-7xl mx-auto px-4 py-20">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          {/* Mission Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 hover:scale-105 transition-all duration-300">
              <div className="text-center mb-6">
                <div className="text-6xl mb-4 animate-pulse">🎯</div>
                <h3 className="text-3xl font-bold text-white mb-4 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent">
                  Sứ Mệnh
                </h3>
              </div>
              <p className="text-white/80 text-lg leading-relaxed mb-8 text-center">
                Tạo ra một nền tảng giáo dục và bảo tồn động vật hoang dã thông qua công nghệ 3D AI tiên tiến, 
                giúp mọi người hiểu rõ hơn về tầm quan trọng của việc bảo vệ thiên nhiên.
              </p>
              <div className="space-y-4">
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                    <span className="text-green-400 text-xl">✅</span>
                  </div>
                  <span className="text-white/90 font-medium">Giáo dục về đa dạng sinh học</span>
                </div>
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                    <span className="text-green-400 text-xl">✅</span>
                  </div>
                  <span className="text-white/90 font-medium">Nâng cao nhận thức bảo tồn</span>
                </div>
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                    <span className="text-green-400 text-xl">✅</span>
                  </div>
                  <span className="text-white/90 font-medium">Ứng dụng công nghệ AI</span>
                </div>
              </div>
            </div>
          </div>

          {/* Vision Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 hover:scale-105 transition-all duration-300">
              <div className="text-center mb-6">
                <div className="text-6xl mb-4 animate-pulse">🔮</div>
                <h3 className="text-3xl font-bold text-white mb-4 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                  Tầm Nhìn
                </h3>
              </div>
              <p className="text-white/80 text-lg leading-relaxed mb-8 text-center">
                Trở thành nền tảng hàng đầu thế giới về giáo dục và bảo tồn động vật hoang dã, 
                sử dụng công nghệ 3D và AI để tạo ra trải nghiệm học tập tương tác và hấp dẫn.
              </p>
              <div className="space-y-4">
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                    <span className="text-blue-400 text-xl">🚀</span>
                  </div>
                  <span className="text-white/90 font-medium">Công nghệ 3D AI tiên tiến</span>
                </div>
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                    <span className="text-blue-400 text-xl">🌐</span>
                  </div>
                  <span className="text-white/90 font-medium">Tiếp cận toàn cầu</span>
                </div>
                <div className="flex items-center space-x-4 p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-all duration-300">
                  <div className="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                    <span className="text-blue-400 text-xl">🤝</span>
                  </div>
                  <span className="text-white/90 font-medium">Hợp tác quốc tế</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Technology Stack */}
      <div className="max-w-7xl mx-auto px-4 py-20">
        <div className="text-center mb-16">
          <h3 className="text-4xl font-bold text-white mb-6 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            🛠️ Công Nghệ Sử Dụng
          </h3>
          <p className="text-xl text-white/70 max-w-2xl mx-auto">
            Những công nghệ tiên tiến đằng sau dự án, tạo nên trải nghiệm tuyệt vời
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* React Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 text-center hover:scale-105 transition-all duration-300">
              <div className="text-6xl mb-6 animate-bounce">⚛️</div>
              <h4 className="text-2xl font-bold text-white mb-4 bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                React
              </h4>
              <p className="text-white/80 text-sm leading-relaxed">
                Framework JavaScript hiện đại để xây dựng giao diện người dùng tương tác và mượt mà
              </p>
            </div>
          </div>
          
          {/* Tailwind CSS Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 text-center hover:scale-105 transition-all duration-300">
              <div className="text-6xl mb-6 animate-bounce">🎨</div>
              <h4 className="text-2xl font-bold text-white mb-4 bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                Tailwind CSS
              </h4>
              <p className="text-white/80 text-sm leading-relaxed">
                Framework CSS utility-first để thiết kế giao diện đẹp mắt và responsive
              </p>
            </div>
          </div>
          
          {/* Three.js Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 text-center hover:scale-105 transition-all duration-300">
              <div className="text-6xl mb-6 animate-bounce">🎮</div>
              <h4 className="text-2xl font-bold text-white mb-4 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Three.js
              </h4>
              <p className="text-white/80 text-sm leading-relaxed">
                Thư viện 3D JavaScript mạnh mẽ để hiển thị mô hình động vật sống động
              </p>
            </div>
          </div>
          
          {/* Meshy AI Card */}
          <div className="group relative">
            <div className="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-red-500/20 rounded-3xl blur-xl group-hover:blur-2xl transition-all duration-500"></div>
            <div className="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-3xl p-8 text-center hover:scale-105 transition-all duration-300">
              <div className="text-6xl mb-6 animate-bounce">🤖</div>
              <h4 className="text-2xl font-bold text-white mb-4 bg-gradient-to-r from-orange-400 to-red-400 bg-clip-text text-transparent">
                Meshy AI
              </h4>
              <p className="text-white/80 text-sm leading-relaxed">
                Công nghệ AI tiên tiến để tạo mô hình 3D từ ảnh với độ chính xác cao
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Team Section */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center mb-12">
          <h3 className="text-3xl font-bold text-white mb-4">👥 Đội Ngũ</h3>
          <p className="text-xl text-white/60">Những người đứng sau dự án</p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl text-white">
              👨‍💻
            </div>
            <h4 className="text-xl font-bold text-white mb-2">Frontend Developer</h4>
            <p className="text-white/70 text-sm mb-4">
              Chuyên gia React và UI/UX design
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>🎨 UI/UX Design</div>
              <div>⚛️ React Development</div>
              <div>🎮 3D Integration</div>
            </div>
          </div>
          
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="w-24 h-24 bg-gradient-to-br from-green-500 to-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl text-white">
              🧠
            </div>
            <h4 className="text-xl font-bold text-white mb-2">AI Specialist</h4>
            <p className="text-white/70 text-sm mb-4">
              Chuyên gia về AI và machine learning
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>🤖 AI Development</div>
              <div>🔬 Research</div>
              <div>📊 Data Analysis</div>
            </div>
          </div>
          
          <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 text-center hover:scale-105 transition-all duration-300">
            <div className="w-24 h-24 bg-gradient-to-br from-pink-500 to-red-600 rounded-full mx-auto mb-4 flex items-center justify-center text-3xl text-white">
              🌱
            </div>
            <h4 className="text-xl font-bold text-white mb-2">Conservation Expert</h4>
            <p className="text-white/70 text-sm mb-4">
              Chuyên gia về bảo tồn động vật hoang dã
            </p>
            <div className="text-xs text-white/50 space-y-1">
              <div>🦁 Wildlife Biology</div>
              <div>🌍 Conservation</div>
              <div>📚 Education</div>
            </div>
          </div>
        </div>
      </div>

      {/* Contact Section */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center mb-12">
          <h3 className="text-3xl font-bold text-white mb-4">📞 Liên Hệ</h3>
          <p className="text-xl text-white/60">Hãy liên hệ với chúng tôi để biết thêm thông tin</p>
        </div>
        
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div className="animate-fade-in-up">
            <h4 className="text-2xl font-bold text-white mb-6">Thông Tin Liên Hệ</h4>
            <div className="space-y-4">
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center">
                  <span className="text-blue-300 text-xl">📧</span>
                </div>
                <div>
                  <div className="text-white font-medium">Email</div>
                  <div className="text-white/60">info@thegioiddongvat.com</div>
                </div>
              </div>
              
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                  <span className="text-green-300 text-xl">📞</span>
                </div>
                <div>
                  <div className="text-white font-medium">Điện thoại</div>
                  <div className="text-white/60">+84 123 456 789</div>
                </div>
              </div>
              
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center">
                  <span className="text-purple-300 text-xl">📍</span>
                </div>
                <div>
                  <div className="text-white font-medium">Địa chỉ</div>
                  <div className="text-white/60">Hà Nội, Việt Nam</div>
                </div>
              </div>
              
              <div className="flex items-center space-x-4">
                <div className="w-12 h-12 bg-pink-500/20 rounded-full flex items-center justify-center">
                  <span className="text-pink-300 text-xl">🌐</span>
                </div>
                <div>
                  <div className="text-white font-medium">Website</div>
                  <div className="text-white/60">www.thegioiddongvat.com</div>
                </div>
              </div>
            </div>
          </div>

          <div className="animate-fade-in-up-delay-1">
            <h4 className="text-2xl font-bold text-white mb-6">Gửi Tin Nhắn</h4>
            <form className="space-y-4">
              <div>
                <input
                  type="text"
                  placeholder="Họ và tên"
                  className="w-full px-4 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:border-blue-400 transition-all duration-300"
                />
              </div>
              <div>
                <input
                  type="email"
                  placeholder="Email"
                  className="w-full px-4 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:border-blue-400 transition-all duration-300"
                />
              </div>
              <div>
                <textarea
                  placeholder="Nội dung tin nhắn"
                  rows="4"
                  className="w-full px-4 py-3 bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:border-blue-400 transition-all duration-300 resize-none"
                ></textarea>
              </div>
              <button
                type="submit"
                className="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105"
              >
                📤 Gửi tin nhắn
              </button>
            </form>
          </div>
        </div>
      </div>

      {/* Footer Info */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center">
          <div className="text-6xl mb-6">🌍</div>
          <h3 className="text-2xl font-bold text-white mb-4">Cảm ơn bạn đã quan tâm!</h3>
          <p className="text-white/60 mb-6">
            Hãy cùng chung tay bảo vệ sự đa dạng sinh học của hành tinh chúng ta
          </p>
          <div className="flex justify-center space-x-6">
            <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center hover:bg-blue-500/30 cursor-pointer transition-all duration-300 hover:scale-110">
              <span className="text-blue-300">📘</span>
            </div>
            <div className="w-12 h-12 bg-pink-500/20 rounded-full flex items-center justify-center hover:bg-pink-500/30 cursor-pointer transition-all duration-300 hover:scale-110">
              <span className="text-pink-300">📷</span>
            </div>
            <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center hover:bg-green-500/30 cursor-pointer transition-all duration-300 hover:scale-110">
              <span className="text-green-300">🐦</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AboutPage;
