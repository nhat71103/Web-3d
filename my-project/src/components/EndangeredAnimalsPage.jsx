import React from 'react';

const EndangeredAnimalsPage = ({ animals, selectedAnimal, setSelectedAnimal, getImageUrl, getModelUrl, getConservationColor, onNavigateToHome }) => {
  // Debug log để kiểm tra data
  console.log('🔍 EndangeredAnimalsPage - animals data:', animals);
  console.log('🔍 EndangeredAnimalsPage - animals length:', animals?.length);
  
  // Debug log để kiểm tra conservation status từ database
  console.log('🔍 Conservation Statuses từ database:');
  animals.forEach((animal, index) => {
    console.log(`Animal ${index + 1}: ${animal.name} - Status: "${animal.conservation_status_name}" - Has3D: ${animal.has3DModel}`);
  });
  
  // Lọc động vật theo tình trạng bảo tồn từ CSDL chuẩn hóa - bao gồm tất cả 6 mức độ
  const endangeredAnimals = animals.filter(animal => 
    animal.conservation_status_name && 
    ['Đã tuyệt chủng', 'Cực kỳ nguy cấp', 'Nguy cấp', 'Dễ bị tổn thương', 'Gần bị đe dọa', 'Ít quan ngại'].includes(animal.conservation_status_name)
  );
  
  // Debug log để kiểm tra logic lọc
  console.log('🔍 EndangeredAnimalsPage - endangeredAnimals:', endangeredAnimals);
  console.log('🔍 EndangeredAnimalsPage - endangeredAnimals length:', endangeredAnimals.length);

  const getConservationLevel = (status) => {
    switch (status) {
      case 'Đã tuyệt chủng': return { level: 'Đã tuyệt chủng', color: 'bg-black', priority: 1 };
      case 'Cực kỳ nguy cấp': return { level: 'Cực kỳ nguy cấp', color: 'bg-red-600', priority: 2 };
      case 'Nguy cấp': return { level: 'Nguy cấp', color: 'bg-orange-500', priority: 3 };
      case 'Dễ bị tổn thương': return { level: 'Dễ bị tổn thương', color: 'bg-yellow-500', priority: 4 };
      case 'Gần bị đe dọa': return { level: 'Gần bị đe dọa', color: 'bg-green-500', priority: 5 };
      case 'Ít quan ngại': return { level: 'Ít quan ngại', color: 'bg-green-600', priority: 6 };
      default: return { level: 'Không xác định', color: 'bg-gray-500', priority: 7 };
    }
  };

  const sortedEndangeredAnimals = [...endangeredAnimals].sort((a, b) => {
    const aLevel = getConservationLevel(a.conservation_status_name);
    const bLevel = getConservationLevel(b.conservation_status_name);
    return aLevel.priority - bLevel.priority;
  });

  return (
    <div className="min-h-screen">


      {/* Hero Section */}
      <div className="relative overflow-hidden bg-gradient-to-br from-red-900/50 to-orange-900/50">
        <div className="absolute inset-0 bg-gradient-to-r from-red-600/20 to-orange-600/20"></div>
        <div className="relative max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
          <div className="text-center text-white z-10 animate-fade-in-up">
            <div className="text-6xl mb-4 animate-pulse">🚨</div>
            <h2 className="text-5xl font-bold mb-4 animate-gradient-text">
              Động Vật Nguy Cấp
            </h2>
            <p className="text-xl text-white/80 mb-4 animate-fade-in-delay-1">
              Những loài cần được bảo vệ khẩn cấp
            </p>
            <p className="text-white/60 animate-fade-in-delay-2">
              Hãy cùng chung tay bảo vệ sự đa dạng sinh học
            </p>
            <div className="mt-6 p-4 bg-red-500/20 rounded-lg border border-red-500/30">
              <p className="text-red-200 text-sm">
                ⚠️ Hiện có <span className="font-bold text-xl">{endangeredAnimals.length}</span> loài động vật được theo dõi
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Conservation Status Legend */}
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6">
          <h3 className="text-2xl font-bold text-white mb-6 text-center">📊 Mức Độ Nguy Cấp</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {['Đã tuyệt chủng', 'Cực kỳ nguy cấp', 'Nguy cấp', 'Dễ bị tổn thương', 'Gần bị đe dọa', 'Ít quan ngại'].map((status) => {
              const level = getConservationLevel(status);
              const count = endangeredAnimals.filter(animal => animal.conservation_status_name === status).length;
              return (
                <div key={status} className="flex items-center space-x-3 p-3 bg-white/5 rounded-lg">
                  <div className={`w-4 h-4 rounded-full ${level.color}`}></div>
                  <div className="flex-1">
                    <div className="text-white font-medium">{level.level}</div>
                    <div className="text-white/60 text-sm">{count} loài</div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Endangered Animals Grid */}
      <div className="max-w-7xl mx-auto px-4 pb-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {sortedEndangeredAnimals.map((animal, index) => {
            const conservationLevel = getConservationLevel(animal.conservation_status_name);
            return (
              <div
                key={animal.id}
                className="group bg-gradient-to-br from-red-500/20 to-orange-500/20 backdrop-blur-lg rounded-2xl p-6 border border-red-500/30 hover:border-red-400/50 transition-all duration-500 hover:transform hover:scale-105 hover:-translate-y-3 cursor-pointer relative overflow-hidden animate-fade-in-up"
                style={{
                  animationDelay: `${index * 100}ms`,
                  animationFillMode: 'both'
                }}
                onClick={() => {
                  setSelectedAnimal(animal);
                  if (onNavigateToHome) {
                    onNavigateToHome();
                  }
                }}
              >
                {/* Priority Badge */}
                <div className={`absolute top-4 right-4 ${conservationLevel.color} text-white text-xs px-2 py-1 rounded-full font-bold z-20`}>
                  #{conservationLevel.priority}
                </div>

                {/* Hover Glow Effect */}
                <div className="absolute inset-0 bg-gradient-to-br from-red-500/0 via-orange-500/0 to-red-500/0 group-hover:from-red-500/10 group-hover:via-orange-500/10 group-hover:to-red-500/10 transition-all duration-500 rounded-2xl"></div>
                
                {/* Card Content */}
                <div className="relative z-10 text-center">
                  <h3 className="text-xl font-bold text-white mb-2 group-hover:text-red-300 transition-colors duration-300">
                    {animal.name}
                  </h3>
                                     <p className="text-white/70 text-sm mb-3">{animal.species_name}</p>
                  
                  {/* Conservation Status */}
                  <div className="mb-4">
                    <span className={`inline-block px-3 py-1 ${conservationLevel.color} text-white rounded-full text-sm font-bold mb-2`}>
                                             🚨 {animal.conservation_status_name}
                    </span>
                    <div className="text-white/80 text-xs">
                      Mức độ: {conservationLevel.level}
                    </div>
                  </div>

                  {/* Habitat and Population */}
                  <div className="space-y-2 mb-4">
                    <span className="inline-block px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-sm">
                                             🌍 {animal.habitat_name || 'Không xác định'}
                    </span>
                    <div className="text-white/60 text-xs">
                      Quần thể: {animal.population_count || 'Không xác định'}
                    </div>
                  </div>

                  {/* 3D Status */}
                  <div className="flex items-center justify-center space-x-2 text-white/60 text-sm">
                    <div className="flex items-center space-x-1">
                      <span className="group-hover:animate-pulse">🎮</span>
                      <span>{animal.has3DModel ? 'Có model 3D' : 'Chưa có model 3D'}</span>
                     </div>
                     {animal.has3DModel && (
                       <span className="text-green-400 group-hover:animate-bounce">✅</span>
                     )}
                  </div>


                </div>
              </div>
            );
          })}
        </div>

        {endangeredAnimals.length === 0 && (
          <div className="text-center py-16">
            <div className="text-6xl mb-4">📊</div>
            <h3 className="text-2xl font-bold text-white mb-2">Chưa có dữ liệu động vật!</h3>
            <p className="text-white/60">Hãy thêm động vật vào cơ sở dữ liệu để xem thông tin bảo tồn</p>
            <div className="mt-4 text-white/40 text-sm">
              <p>💡 Để thêm động vật, hãy sử dụng trang Admin để cập nhật thông tin</p>
            </div>
          </div>
        )}
      </div>

      {/* Conservation Actions */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="text-center mb-16">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-green-500 to-blue-500 rounded-full mb-6 animate-pulse">
            <span className="text-4xl">🌍</span>
          </div>
          <h3 className="text-4xl font-bold text-white mb-4 bg-gradient-to-r from-green-400 to-blue-400 bg-clip-text text-transparent">
            Hành Động Bảo Tồn
          </h3>
          <p className="text-xl text-white/80 mb-2">Bạn có thể làm gì để bảo vệ động vật hoang dã?</p>
          <p className="text-white/60">Hãy cùng chung tay tạo nên sự khác biệt tích cực</p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {/* Học Hỏi */}
          <div className="group bg-gradient-to-br from-blue-500/20 to-purple-500/20 backdrop-blur-lg border border-blue-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">📚</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-blue-300 transition-colors duration-300">Học Hỏi & Nghiên Cứu</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Tìm hiểu sâu về các loài động vật, môi trường sống và những mối đe dọa chúng đang phải đối mặt
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                  Đọc sách và tài liệu khoa học
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                  Theo dõi các chương trình tài liệu
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
                  Tham gia các khóa học bảo tồn
                </div>
              </div>
            </div>
          </div>

          {/* Giảm Thiểu */}
          <div className="group bg-gradient-to-br from-green-500/20 to-emerald-500/20 backdrop-blur-lg border border-green-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-green-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-green-500/10 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">♻️</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-green-300 transition-colors duration-300">Giảm Thiểu & Tái Chế</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Thay đổi lối sống để giảm tác động tiêu cực đến môi trường và động vật hoang dã
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-green-400 rounded-full mr-3"></span>
                  Sử dụng túi vải thay túi nilon
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-green-400 rounded-full mr-3"></span>
                  Tái chế rác thải đúng cách
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-green-400 rounded-full mr-3"></span>
                  Chọn sản phẩm thân thiện môi trường
                </div>
              </div>
            </div>
          </div>

          {/* Trồng Cây */}
          <div className="group bg-gradient-to-br from-emerald-500/20 to-teal-500/20 backdrop-blur-lg border border-emerald-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">🌱</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-emerald-300 transition-colors duration-300">Trồng Cây & Tạo Môi Trường</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Tạo ra môi trường sống lành mạnh cho động vật hoang dã và cải thiện chất lượng không khí
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-emerald-400 rounded-full mr-3"></span>
                  Trồng cây bản địa trong vườn
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-emerald-400 rounded-full mr-3"></span>
                  Tham gia hoạt động trồng rừng
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-emerald-400 rounded-full mr-3"></span>
                  Tạo vườn thu hút chim và côn trùng
                </div>
              </div>
            </div>
          </div>

          {/* Lan Tỏa */}
          <div className="group bg-gradient-to-br from-orange-500/20 to-red-500/20 backdrop-blur-lg border border-orange-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-orange-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-red-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">📢</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-orange-300 transition-colors duration-300">Lan Tỏa & Vận Động</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Chia sẻ kiến thức và vận động cộng đồng cùng tham gia bảo vệ động vật hoang dã
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-orange-400 rounded-full mr-3"></span>
                  Chia sẻ trên mạng xã hội
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-orange-400 rounded-full mr-3"></span>
                  Tham gia các chiến dịch bảo tồn
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-orange-400 rounded-full mr-3"></span>
                  Giáo dục trẻ em về bảo tồn
                </div>
              </div>
            </div>
          </div>

          {/* Hỗ Trợ Tài Chính */}
          <div className="group bg-gradient-to-br from-purple-500/20 to-pink-500/20 backdrop-blur-lg border border-purple-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">💝</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-purple-300 transition-colors duration-300">Hỗ Trợ Tài Chính</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Đóng góp tài chính cho các tổ chức bảo tồn và dự án nghiên cứu động vật hoang dã
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-purple-400 rounded-full mr-3"></span>
                  Quyên góp cho tổ chức bảo tồn
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-purple-400 rounded-full mr-3"></span>
                  Hỗ trợ dự án nghiên cứu
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-purple-400 rounded-full mr-3"></span>
                  Mua sản phẩm từ các dự án bảo tồn
                </div>
              </div>
            </div>
          </div>

          {/* Du Lịch Có Trách Nhiệm */}
          <div className="group bg-gradient-to-br from-cyan-500/20 to-blue-500/20 backdrop-blur-lg border border-cyan-400/30 rounded-3xl p-8 hover:scale-105 hover:shadow-2xl hover:shadow-cyan-500/25 transition-all duration-500 relative overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div className="relative z-10">
              <div className="text-6xl mb-6 group-hover:scale-110 transition-transform duration-300">🌍</div>
              <h4 className="text-2xl font-bold text-white mb-4 group-hover:text-cyan-300 transition-colors duration-300">Du Lịch Có Trách Nhiệm</h4>
              <p className="text-white/80 mb-6 leading-relaxed">
                Lựa chọn các hoạt động du lịch thân thiện với môi trường và không gây hại đến động vật
              </p>
              <div className="space-y-3">
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-cyan-400 rounded-full mr-3"></span>
                  Chọn tour du lịch sinh thái
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-cyan-400 rounded-full mr-3"></span>
                  Tránh các hoạt động cưỡng ép động vật
                </div>
                <div className="flex items-center text-sm text-white/70">
                  <span className="w-2 h-2 bg-cyan-400 rounded-full mr-3"></span>
                  Tôn trọng môi trường tự nhiên
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  );
};

export default EndangeredAnimalsPage;
